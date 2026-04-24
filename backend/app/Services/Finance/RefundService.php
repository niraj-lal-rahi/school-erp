<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\RefundData;
use App\Enums\Finance\FeeInstallmentStatus;
use App\Enums\Finance\FeeInvoiceStatus;
use App\Enums\Finance\PaymentStatus;
use App\Enums\Finance\RefundStatus;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment;
use App\Models\Finance\PaymentAllocation;
use App\Models\Finance\Refund;
use App\Repositories\Contracts\Finance\RefundRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        protected RefundRepositoryInterface $refunds,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->refunds->paginate($filters, $perPage);
    }

    public function create(RefundData $data): Refund
    {
        $payment = Payment::query()->findOrFail((int) $data->attributes['payment_id']);
        if ((int) $payment->student_id !== (int) $data->attributes['student_id']) {
            throw ValidationException::withMessages([
                'student_id' => 'The selected payment does not belong to the selected student.',
            ]);
        }

        $this->guardRefundAmount((int) $data->attributes['payment_id'], (float) $data->attributes['amount']);

        return DB::transaction(fn (): Refund => $this->refunds->create(RefundData::fromArray([
            ...$data->attributes,
            'refund_no' => $this->refunds->nextRefundNumber((int) $data->attributes['school_id']),
            'status' => RefundStatus::Requested->value,
        ])));
    }

    public function approve(Refund $refund, int $approvedBy): Refund
    {
        if ($refund->status !== RefundStatus::Requested->value) {
            throw ValidationException::withMessages([
                'status' => 'Only requested refunds can be approved.',
            ]);
        }

        return DB::transaction(fn (): Refund => $this->refunds->update($refund, [
            'status' => RefundStatus::Approved->value,
            'approved_by' => $approvedBy,
        ]));
    }

    public function process(Refund $refund, int $processedBy): Refund
    {
        if ($refund->status !== RefundStatus::Approved->value) {
            throw ValidationException::withMessages([
                'status' => 'Only approved refunds can be processed.',
            ]);
        }

        $payment = Payment::query()->findOrFail($refund->payment_id);
        $this->guardRefundAmount($payment->id, (float) $refund->amount, $refund->id);

        return DB::transaction(function () use ($refund, $processedBy, $payment): Refund {
            $updated = $this->refunds->update($refund, [
                'status' => RefundStatus::Processed->value,
                'processed_by' => $processedBy,
            ]);

            $payment->loadMissing('allocations');
            $remaining = (float) $updated->amount;
            foreach ($payment->allocations as $allocation) {
                if ($remaining <= 0) {
                    break;
                }

                $reversalAmount = min($remaining, (float) $allocation->allocated_amount);
                if ($reversalAmount <= 0) {
                    continue;
                }

                $this->reverseAllocation($allocation, $reversalAmount);
                $remaining -= $reversalAmount;
            }

            $processedTotal = $this->refunds->totalProcessedAmountForPayment($payment->id);
            if ($processedTotal >= (float) $payment->amount) {
                $payment->update(['status' => PaymentStatus::Refunded->value]);
            } else {
                $payment->update(['status' => PaymentStatus::Successful->value]);
            }

            return $updated;
        });
    }

    public function reject(Refund $refund): Refund
    {
        return DB::transaction(fn (): Refund => $this->refunds->update($refund, [
            'status' => RefundStatus::Rejected->value,
        ]));
    }

    public function delete(Refund $refund): void
    {
        if (! in_array($refund->status, [RefundStatus::Requested->value, RefundStatus::Rejected->value, RefundStatus::Cancelled->value], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only requested, rejected, or cancelled refunds can be deleted.',
            ]);
        }

        DB::transaction(fn (): bool => tap($refund)->delete());
    }

    protected function guardRefundAmount(int $paymentId, float $amount, ?int $ignoreRefundId = null): void
    {
        $payment = Payment::query()->findOrFail($paymentId);
        if ($payment->status !== PaymentStatus::Successful->value && $payment->status !== PaymentStatus::Refunded->value) {
            throw ValidationException::withMessages([
                'payment_id' => 'Refunds can only be requested for successful payments.',
            ]);
        }

        $processedAmount = $this->refunds->totalProcessedAmountForPayment($paymentId, $ignoreRefundId);
        if ($processedAmount + $amount > (float) $payment->amount) {
            throw ValidationException::withMessages([
                'amount' => 'Refund amount exceeds the available paid amount.',
            ]);
        }
    }

    protected function reverseAllocation(PaymentAllocation $allocation, float $amount): void
    {
        $invoice = FeeInvoice::query()->lockForUpdate()->findOrFail($allocation->fee_invoice_id);
        $invoicePaid = max(0, (float) $invoice->paid_amount - $amount);
        $invoiceBalance = max(0, (float) $invoice->grand_total - $invoicePaid);

        $invoice->update([
            'paid_amount' => $invoicePaid,
            'balance_amount' => $invoiceBalance,
            'status' => $invoicePaid <= 0
                ? FeeInvoiceStatus::Issued->value
                : ($invoiceBalance <= 0 ? FeeInvoiceStatus::Paid->value : FeeInvoiceStatus::PartiallyPaid->value),
        ]);

        if ($allocation->fee_installment_id) {
            $installment = FeeInstallment::query()->lockForUpdate()->findOrFail($allocation->fee_installment_id);
            $installmentPaid = max(0, (float) $installment->paid_amount - $amount);
            $installmentBalance = max(
                0,
                ((float) $installment->amount - (float) $installment->discount_amount + (float) $installment->fine_amount) - $installmentPaid
            );

            $installment->update([
                'paid_amount' => $installmentPaid,
                'balance_amount' => $installmentBalance,
                'status' => $installmentPaid <= 0
                    ? FeeInstallmentStatus::Pending->value
                    : ($installmentBalance <= 0 ? FeeInstallmentStatus::Paid->value : FeeInstallmentStatus::PartiallyPaid->value),
            ]);
        }
    }
}
