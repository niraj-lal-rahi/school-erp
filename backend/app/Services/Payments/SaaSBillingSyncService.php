<?php

namespace App\Services\Payments;

use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;
use App\Models\Saas\TenantBillingRecord;
use App\Models\Saas\TenantSubscription;
use Illuminate\Support\Facades\DB;

class SaaSBillingSyncService
{
    public function syncSuccessfulPayment(PaymentTransaction $transaction): ?TenantBillingRecord
    {
        if ($transaction->payable_type !== 'saas_subscription') {
            return null;
        }

        /** @var TenantBillingRecord|null $billingRecord */
        $billingRecord = $transaction->payable_id
            ? TenantBillingRecord::query()->find($transaction->payable_id)
            : null;

        /** @var TenantSubscription|null $subscription */
        $subscription = $transaction->tenantSubscription ?: (
            $billingRecord?->subscription
        );

        if (! $billingRecord && ! $subscription) {
            return null;
        }

        return DB::transaction(function () use ($billingRecord, $subscription): ?TenantBillingRecord {
            if ($billingRecord) {
                $billingRecord->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'end_date' => $subscription->billing_cycle === 'yearly'
                        ? now()->addYear()->toDateString()
                        : now()->addMonth()->toDateString(),
                ]);
            }

            return $billingRecord?->refresh();
        });
    }

    public function syncRefund(PaymentTransaction $transaction, PaymentRefund $paymentRefund): void
    {
        if ($transaction->payable_type !== 'saas_subscription' || ! $transaction->payable_id) {
            return;
        }

        $billingRecord = TenantBillingRecord::query()->find($transaction->payable_id);

        if (! $billingRecord) {
            return;
        }

        DB::transaction(function () use ($billingRecord, $paymentRefund): void {
            $billingRecord->update([
                'status' => $paymentRefund->status === 'successful' ? 'failed' : $billingRecord->status,
            ]);
        });
    }
}
