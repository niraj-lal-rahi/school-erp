<?php

namespace App\Services\Payments;

use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment;
use App\Models\Finance\PaymentAllocation;
use App\Models\Finance\Receipt;
use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class FinancePaymentSyncService
{
    public function syncSuccessfulPayment(PaymentTransaction $transaction): ?Payment
    {
        if ($transaction->payable_type !== 'school_fee' || ! $transaction->payable_id) {
            return null;
        }

        /** @var FeeInvoice|null $invoice */
        $invoice = FeeInvoice::withoutGlobalScopes()->find($transaction->payable_id);

        if (! $invoice) {
            return null;
        }

        return DB::transaction(function () use ($transaction, $invoice): Payment {
            $payment = Payment::withoutGlobalScopes()->firstOrCreate(
                [
                    'school_id' => $transaction->school_id,
                    'gateway_transaction_id' => $transaction->gateway_payment_id ?: $transaction->transaction_no,
                ],
                [
                    'payment_no' => $this->generatePaymentNo($transaction),
                    'student_id' => $transaction->student_id ?? $invoice->student_id,
                    'fee_invoice_id' => $invoice->id,
                    'payment_date' => ($transaction->paid_at ?? now())->toDateString(),
                    'payment_method' => $transaction->payment_method,
                    'gateway_provider' => $transaction->provider,
                    'reference_no' => $transaction->upi_reference_no ?: $transaction->transaction_no,
                    'amount' => $transaction->amount,
                    'status' => 'confirmed',
                    'confirmed_at' => $transaction->verified_at ?? now(),
                    'remarks' => 'Synced from payment gateway transaction '.$transaction->transaction_no,
                ]
            );

            if (! PaymentAllocation::withoutGlobalScopes()
                ->where('payment_id', $payment->id)
                ->where('fee_invoice_id', $invoice->id)
                ->exists()) {
                PaymentAllocation::withoutGlobalScopes()->create([
                    'school_id' => $transaction->school_id,
                    'payment_id' => $payment->id,
                    'fee_invoice_id' => $invoice->id,
                    'allocated_amount' => $transaction->amount,
                ]);
            }

            $paidAmount = (float) $invoice->payments()
                ->withoutGlobalScopes()
                ->where('status', 'confirmed')
                ->sum('amount');
            $balance = max((float) $invoice->grand_total - $paidAmount, 0);

            $invoice->update([
                'paid_amount' => $paidAmount,
                'balance_amount' => $balance,
                'status' => $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : $invoice->status),
            ]);

            Receipt::withoutGlobalScopes()->firstOrCreate(
                ['payment_id' => $payment->id],
                [
                    'school_id' => $transaction->school_id,
                    'receipt_no' => $this->generateReceiptNo($payment),
                    'student_id' => $payment->student_id,
                    'receipt_date' => ($transaction->paid_at ?? now())->toDateString(),
                    'amount' => $payment->amount,
                ]
            );

            return $payment->refresh()->load(['invoice', 'receipt', 'allocations']);
        });
    }

    public function syncRefund(PaymentTransaction $transaction, PaymentRefund $paymentRefund): void
    {
        if ($transaction->payable_type !== 'school_fee' || ! $transaction->payable_id) {
            return;
        }

        $payment = Payment::withoutGlobalScopes()
            ->where('school_id', $transaction->school_id)
            ->where('gateway_transaction_id', $transaction->gateway_payment_id ?: $transaction->transaction_no)
            ->first();

        if (! $payment) {
            return;
        }

        $invoice = $payment->invoice;

        if (! $invoice) {
            return;
        }

        DB::transaction(function () use ($payment, $invoice, $paymentRefund): void {
            $refundedAmount = (float) ($payment->refunds()->withoutGlobalScopes()->sum('amount') + $paymentRefund->amount);
            $netPaid = max((float) $payment->amount - $refundedAmount, 0);

            $payment->update([
                'status' => $netPaid <= 0 ? 'refunded' : $payment->status,
            ]);

            $invoicePaid = max((float) $invoice->paid_amount - (float) $paymentRefund->amount, 0);
            $balance = max((float) $invoice->grand_total - $invoicePaid, 0);

            $invoice->update([
                'paid_amount' => $invoicePaid,
                'balance_amount' => $balance,
                'status' => $invoicePaid <= 0 ? 'pending' : ($balance > 0 ? 'partial' : 'paid'),
            ]);
        });
    }

    protected function generatePaymentNo(PaymentTransaction $transaction): string
    {
        return 'PAY-'.$transaction->school_id.'-'.$transaction->id;
    }

    protected function generateReceiptNo(Payment $payment): string
    {
        return 'RCT-'.$payment->school_id.'-'.$payment->id;
    }
}
