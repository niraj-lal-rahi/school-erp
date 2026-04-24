<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\FeeInvoiceData;
use App\Enums\Finance\FeeInstallmentStatus;
use App\Enums\Finance\FeeInvoiceStatus;
use App\Enums\Finance\FineType;
use App\Enums\Finance\StudentDiscountStatus;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FineRule;
use App\Models\Finance\StudentDiscount;
use App\Repositories\Contracts\Finance\FeeInvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeInvoiceService
{
    public function __construct(
        protected FeeInvoiceRepositoryInterface $invoices,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->invoices->paginate($filters, $perPage);
    }

    public function create(FeeInvoiceData $data): FeeInvoice
    {
        [$attributes, $items] = $this->prepareInvoicePayload($data);

        return DB::transaction(fn (): FeeInvoice => $this->invoices->create(FeeInvoiceData::fromArray($attributes), $items));
    }

    public function update(FeeInvoice $invoice, FeeInvoiceData $data): FeeInvoice
    {
        if ($invoice->status !== FeeInvoiceStatus::Draft->value) {
            throw ValidationException::withMessages([
                'status' => 'Only draft invoices can be updated.',
            ]);
        }

        [$attributes, $items] = $this->prepareInvoicePayload($data, $invoice);

        return DB::transaction(fn (): FeeInvoice => $this->invoices->update($invoice, FeeInvoiceData::fromArray($attributes), $items));
    }

    public function delete(FeeInvoice $invoice): void
    {
        if (! in_array($invoice->status, [FeeInvoiceStatus::Draft->value, FeeInvoiceStatus::Cancelled->value], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only draft or cancelled invoices can be deleted.',
            ]);
        }

        DB::transaction(fn (): bool => $invoice->delete());
    }

    public function issue(FeeInvoice $invoice): FeeInvoice
    {
        if ($invoice->status !== FeeInvoiceStatus::Draft->value) {
            throw ValidationException::withMessages([
                'status' => 'Only draft invoices can be issued.',
            ]);
        }

        return DB::transaction(function () use ($invoice): FeeInvoice {
            $invoice->update([
                'status' => $invoice->balance_amount <= 0
                    ? FeeInvoiceStatus::Paid->value
                    : FeeInvoiceStatus::Issued->value,
            ]);

            return $invoice->refresh()->load(['student', 'academicYear', 'creator', 'items.feeHead', 'items.installment']);
        });
    }

    public function cancel(FeeInvoice $invoice): FeeInvoice
    {
        if ((float) $invoice->paid_amount > 0) {
            throw ValidationException::withMessages([
                'status' => 'Paid invoices cannot be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($invoice): FeeInvoice {
            $invoice->update(['status' => FeeInvoiceStatus::Cancelled->value]);

            return $invoice->refresh()->load(['student', 'academicYear', 'creator', 'items.feeHead', 'items.installment']);
        });
    }

    public function applyDiscount(FeeInvoice $invoice, StudentDiscount $studentDiscount): FeeInvoice
    {
        if ($studentDiscount->status !== StudentDiscountStatus::Approved->value) {
            throw ValidationException::withMessages([
                'student_discount_id' => 'Only approved student discounts can be applied.',
            ]);
        }

        if ((int) $studentDiscount->student_id !== (int) $invoice->student_id) {
            throw ValidationException::withMessages([
                'student_discount_id' => 'The selected discount does not belong to this invoice student.',
            ]);
        }

        $discountAmount = min((float) $studentDiscount->discount_amount, (float) $invoice->balance_amount);

        return DB::transaction(function () use ($invoice, $studentDiscount, $discountAmount): FeeInvoice {
            $invoice->loadMissing('items');
            $targetItem = $studentDiscount->fee_head_id
                ? $invoice->items->firstWhere('fee_head_id', $studentDiscount->fee_head_id)
                : $invoice->items->first();

            if ($targetItem) {
                $targetItem->update([
                    'discount_amount' => (float) $targetItem->discount_amount + $discountAmount,
                    'total_amount' => max(0, (float) $targetItem->total_amount - $discountAmount),
                ]);

                if ($targetItem->fee_installment_id) {
                    $installment = FeeInstallment::query()->lockForUpdate()->find($targetItem->fee_installment_id);
                    if ($installment) {
                        $baseAmount = (float) $installment->amount + (float) $installment->fine_amount;
                        $installmentDiscount = (float) $installment->discount_amount + $discountAmount;

                        $installment->update([
                            'discount_amount' => $installmentDiscount,
                            'balance_amount' => max(0, $baseAmount - $installmentDiscount - (float) $installment->paid_amount),
                        ]);
                    }
                }
            }

            $invoice->update([
                'discount_total' => (float) $invoice->discount_total + $discountAmount,
                'grand_total' => max(0, (float) $invoice->grand_total - $discountAmount),
                'balance_amount' => max(0, ((float) $invoice->grand_total - $discountAmount) - (float) $invoice->paid_amount),
            ]);

            return $invoice->refresh()->load(['student', 'academicYear', 'creator', 'items.feeHead', 'items.installment']);
        });
    }

    public function applyFine(FeeInvoice $invoice, FineRule $fineRule): FeeInvoice
    {
        $fineAmount = $this->calculateFineFromRule($invoice, $fineRule);

        return DB::transaction(function () use ($invoice, $fineRule, $fineAmount): FeeInvoice {
            $invoice->loadMissing('items');
            $targetItem = $fineRule->fee_head_id
                ? $invoice->items->firstWhere('fee_head_id', $fineRule->fee_head_id)
                : $invoice->items->first();

            if ($targetItem) {
                $targetItem->update([
                    'fine_amount' => (float) $targetItem->fine_amount + $fineAmount,
                    'total_amount' => (float) $targetItem->total_amount + $fineAmount,
                ]);

                if ($targetItem->fee_installment_id) {
                    $installment = FeeInstallment::query()->lockForUpdate()->find($targetItem->fee_installment_id);
                    if ($installment) {
                        $baseAmount = (float) $installment->amount - (float) $installment->discount_amount;
                        $installmentFine = (float) $installment->fine_amount + $fineAmount;

                        $installment->update([
                            'fine_amount' => $installmentFine,
                            'balance_amount' => max(0, $baseAmount + $installmentFine - (float) $installment->paid_amount),
                        ]);
                    }
                }
            }

            $invoice->update([
                'fine_total' => (float) $invoice->fine_total + $fineAmount,
                'grand_total' => (float) $invoice->grand_total + $fineAmount,
                'balance_amount' => max(0, ((float) $invoice->grand_total + $fineAmount) - (float) $invoice->paid_amount),
            ]);

            return $invoice->refresh()->load(['student', 'academicYear', 'creator', 'items.feeHead', 'items.installment']);
        });
    }

    protected function prepareInvoicePayload(FeeInvoiceData $data, ?FeeInvoice $invoice = null): array
    {
        $attributes = $data->attributes;
        $installmentIds = array_values(array_unique(array_map('intval', $data->installmentIds)));

        if ($installmentIds === []) {
            throw ValidationException::withMessages([
                'installment_ids' => 'At least one installment is required to create an invoice.',
            ]);
        }

        $installments = FeeInstallment::query()
            ->with(['feeHead', 'studentFeeAssignment.student'])
            ->whereIn('id', $installmentIds)
            ->orderBy('due_date')
            ->get();

        if ($installments->count() !== count($installmentIds)) {
            throw ValidationException::withMessages([
                'installment_ids' => 'One or more installments could not be found.',
            ]);
        }

        $studentIds = $installments->pluck('studentFeeAssignment.student_id')->filter()->unique();
        $academicYearIds = $installments->pluck('studentFeeAssignment.academic_year_id')->filter()->unique();

        if ($studentIds->count() !== 1 || (int) $studentIds->first() !== (int) $attributes['student_id']) {
            throw ValidationException::withMessages([
                'installment_ids' => 'All installments must belong to the selected student.',
            ]);
        }

        if ($academicYearIds->count() !== 1 || (int) $academicYearIds->first() !== (int) $attributes['academic_year_id']) {
            throw ValidationException::withMessages([
                'installment_ids' => 'All installments must belong to the selected academic year.',
            ]);
        }

        if ($installments->contains(fn (FeeInstallment $installment) => in_array($installment->status, [FeeInstallmentStatus::Paid->value, FeeInstallmentStatus::Cancelled->value], true))) {
            throw ValidationException::withMessages([
                'installment_ids' => 'Paid or cancelled installments cannot be invoiced.',
            ]);
        }

        $linkedIds = $this->invoices->linkedInstallmentIds($installmentIds, $invoice?->id)->all();
        if ($linkedIds !== []) {
            throw ValidationException::withMessages([
                'installment_ids' => 'One or more installments are already linked to another invoice.',
            ]);
        }

        $subtotal = (float) $installments->sum('amount');
        $discountTotal = (float) $installments->sum('discount_amount');
        $fineTotal = (float) $installments->sum('fine_amount');
        $taxTotal = 0.0;
        $grandTotal = max(0, $subtotal - $discountTotal + $fineTotal + $taxTotal);

        $invoiceAttributes = [
            ...$attributes,
            'invoice_no' => $attributes['invoice_no'] ?? ($invoice?->invoice_no ?: $this->invoices->nextInvoiceNumber((int) $attributes['school_id'])),
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'fine_total' => $fineTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
            'paid_amount' => $invoice?->paid_amount ?? 0,
            'balance_amount' => max(0, $grandTotal - (float) ($invoice?->paid_amount ?? 0)),
            'status' => $invoice?->status ?? FeeInvoiceStatus::Draft->value,
        ];

        $items = $installments->map(fn (FeeInstallment $installment): array => [
            'school_id' => $installment->school_id,
            'fee_installment_id' => $installment->id,
            'fee_head_id' => $installment->fee_head_id,
            'description' => $installment->installment_name,
            'amount' => $installment->amount,
            'discount_amount' => $installment->discount_amount,
            'fine_amount' => $installment->fine_amount,
            'total_amount' => (float) $installment->amount - (float) $installment->discount_amount + (float) $installment->fine_amount,
        ])->all();

        return [$invoiceAttributes, $items];
    }

    protected function calculateFineFromRule(FeeInvoice $invoice, FineRule $fineRule): float
    {
        $dueDate = $invoice->due_date?->copy();
        if (! $dueDate) {
            return (float) $fineRule->amount;
        }

        $daysLate = max(0, now()->startOfDay()->diffInDays($dueDate->startOfDay(), false) * -1 - (int) $fineRule->grace_days);
        if ($daysLate <= 0) {
            return 0.0;
        }

        $baseAmount = match ($fineRule->fine_type) {
            FineType::Daily->value => (float) $fineRule->amount * $daysLate,
            FineType::Percentage->value => ((float) $invoice->balance_amount * (float) $fineRule->amount) / 100,
            default => (float) $fineRule->amount,
        };

        if ($fineRule->max_fine_amount !== null) {
            return min($baseAmount, (float) $fineRule->max_fine_amount);
        }

        return $baseAmount;
    }
}
