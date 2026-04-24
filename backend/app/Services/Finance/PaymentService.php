<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\PaymentData;
use App\Enums\Finance\FeeInstallmentStatus;
use App\Enums\Finance\FeeInvoiceStatus;
use App\Enums\Finance\PaymentMethod;
use App\Enums\Finance\PaymentStatus;
use App\Events\Finance\InvoicePaid;
use App\Events\Finance\PaymentSuccessful;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment;
use App\Models\Finance\PaymentAllocation;
use App\Repositories\Contracts\Finance\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $payments,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->payments->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Payment
    {
        return $this->payments->findOrFail($id);
    }

    public function collect(PaymentData $data): Payment
    {
        $payload = $this->normalizePayload($data->attributes, $data->allocations);
        $status = $payload['status'];

        return DB::transaction(function () use ($payload, $status): Payment {
            $payment = $this->payments->create(PaymentData::fromArray($payload));

            if ($status === PaymentStatus::Successful->value) {
                $payment = $this->applySuccessfulPayment($payment);
            }

            return $payment;
        });
    }

    public function update(Payment $payment, array $attributes): Payment
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payments can be updated.',
            ]);
        }

        return DB::transaction(fn (): Payment => $this->payments->update($payment, $attributes));
    }

    public function confirm(Payment $payment, array $attributes = []): Payment
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payments can be confirmed.',
            ]);
        }

        return DB::transaction(function () use ($payment, $attributes): Payment {
            $payment = $this->payments->update($payment, [
                ...array_filter($attributes, fn ($value) => $value !== null),
                'status' => PaymentStatus::Successful->value,
                'confirmed_at' => now(),
            ]);

            return $this->applySuccessfulPayment($payment);
        });
    }

    public function fail(Payment $payment, array $attributes = []): Payment
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payments can be marked as failed.',
            ]);
        }

        return DB::transaction(fn (): Payment => $this->payments->update($payment, [
            ...array_filter($attributes, fn ($value) => $value !== null),
            'status' => PaymentStatus::Failed->value,
            'failed_at' => now(),
        ]));
    }

    public function delete(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::Successful->value) {
            throw ValidationException::withMessages([
                'status' => 'Successful payments cannot be deleted.',
            ]);
        }

        DB::transaction(fn (): bool => tap($payment)->delete());
    }

    protected function normalizePayload(array $attributes, array $allocations): array
    {
        if ($allocations === []) {
            throw ValidationException::withMessages([
                'allocations' => 'At least one allocation is required.',
            ]);
        }

        $sum = collect($allocations)->sum(fn (array $allocation) => (float) ($allocation['allocated_amount'] ?? 0));
        $amount = (float) ($attributes['amount'] ?? 0);
        if (round($sum, 2) !== round($amount, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount must equal the sum of allocation amounts.',
            ]);
        }

        $studentId = (int) ($attributes['student_id'] ?? 0);
        $invoiceId = $attributes['fee_invoice_id'] ?? null;

        foreach ($allocations as $index => $allocation) {
            $invoice = FeeInvoice::query()->findOrFail((int) $allocation['fee_invoice_id']);
            if ($studentId !== (int) $invoice->student_id) {
                throw ValidationException::withMessages([
                    "allocations.{$index}.fee_invoice_id" => 'Allocated invoice must belong to the selected student.',
                ]);
            }

            if ($invoiceId && (int) $invoiceId !== (int) $invoice->id) {
                throw ValidationException::withMessages([
                    "allocations.{$index}.fee_invoice_id" => 'Allocation invoice does not match the selected payment invoice.',
                ]);
            }

            if (isset($allocation['fee_installment_id']) && $allocation['fee_installment_id']) {
                $installment = FeeInstallment::query()->findOrFail((int) $allocation['fee_installment_id']);
                if ((float) $allocation['allocated_amount'] > (float) $installment->balance_amount) {
                    throw ValidationException::withMessages([
                        "allocations.{$index}.allocated_amount" => 'Allocated amount exceeds installment balance.',
                    ]);
                }
            }

            if ((float) $allocation['allocated_amount'] > (float) $invoice->balance_amount) {
                throw ValidationException::withMessages([
                    "allocations.{$index}.allocated_amount" => 'Allocated amount exceeds invoice balance.',
                ]);
            }
        }

        $method = $attributes['payment_method'] ?? PaymentMethod::Cash->value;
        $status = $attributes['status'] ?? ($method === PaymentMethod::OnlineGateway->value
            ? PaymentStatus::Pending->value
            : PaymentStatus::Successful->value);

        return [
            ...$attributes,
            'payment_no' => $attributes['payment_no'] ?? $this->payments->nextPaymentNumber((int) $attributes['school_id']),
            'status' => $status,
            'allocations' => collect($allocations)->map(fn (array $allocation) => [
                'school_id' => $attributes['school_id'],
                'fee_invoice_id' => (int) $allocation['fee_invoice_id'],
                'fee_invoice_item_id' => $allocation['fee_invoice_item_id'] ?? null,
                'fee_installment_id' => $allocation['fee_installment_id'] ?? null,
                'allocated_amount' => (float) $allocation['allocated_amount'],
            ])->all(),
        ];
    }

    protected function applySuccessfulPayment(Payment $payment): Payment
    {
        $payment->loadMissing(['allocations.invoice', 'allocations.installment']);

        foreach ($payment->allocations as $allocation) {
            $this->applyAllocation($allocation);
        }

        $payment = $this->payments->update($payment, [
            'status' => PaymentStatus::Successful->value,
            'confirmed_at' => $payment->confirmed_at ?? now(),
        ]);

        event(new PaymentSuccessful($payment));

        return $this->payments->findOrFail($payment->id);
    }

    protected function applyAllocation(PaymentAllocation $allocation): void
    {
        $invoice = $allocation->invoice()->lockForUpdate()->firstOrFail();
        $allocated = (float) $allocation->allocated_amount;

        $invoicePaid = (float) $invoice->paid_amount + $allocated;
        $invoiceBalance = max(0, (float) $invoice->grand_total - $invoicePaid);
        $invoiceStatus = $invoiceBalance <= 0
            ? FeeInvoiceStatus::Paid->value
            : FeeInvoiceStatus::PartiallyPaid->value;

        $invoice->update([
            'paid_amount' => $invoicePaid,
            'balance_amount' => $invoiceBalance,
            'status' => $invoiceStatus,
        ]);

        if ($allocation->fee_installment_id) {
            $installment = $allocation->installment()->lockForUpdate()->firstOrFail();
            $installmentPaid = (float) $installment->paid_amount + $allocated;
            $installmentBalance = max(0, ((float) $installment->amount - (float) $installment->discount_amount + (float) $installment->fine_amount) - $installmentPaid);
            $installmentStatus = $installmentBalance <= 0
                ? FeeInstallmentStatus::Paid->value
                : FeeInstallmentStatus::PartiallyPaid->value;

            $installment->update([
                'paid_amount' => $installmentPaid,
                'balance_amount' => $installmentBalance,
                'status' => $installmentStatus,
            ]);
        }

        if ($invoiceStatus === FeeInvoiceStatus::Paid->value) {
            event(new InvoicePaid($invoice->refresh()));
        }
    }
}
