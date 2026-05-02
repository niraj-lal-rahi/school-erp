<?php

namespace App\Services\Saas;

use App\Models\Saas\Tenant;
use App\Models\Saas\TenantBillingRecord;
use App\Models\Saas\TenantSubscription;
use App\Repositories\Contracts\Saas\TenantBillingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantBillingService
{
    public function __construct(
        protected TenantBillingRepositoryInterface $billing,
        protected TenantAuditService $audit,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->billing->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): TenantBillingRecord
    {
        return $this->billing->findOrFail($id);
    }

    public function listByTenant(Tenant $tenant): Collection
    {
        return $this->billing->listByTenant($tenant->id);
    }

    public function generateBillingRecord(Tenant $tenant, TenantSubscription $subscription): TenantBillingRecord
    {
        $subscription->loadMissing('subscriptionPlan');

        $amount = $subscription->billing_cycle === 'yearly'
            ? ($subscription->subscriptionPlan->price_yearly ?? $subscription->subscriptionPlan->price_monthly)
            : $subscription->subscriptionPlan->price_monthly;

        return $this->billing->create([
            'school_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_no' => $this->generateInvoiceNumber($tenant->id),
            'amount' => $amount,
            'currency' => $subscription->subscriptionPlan->currency,
            'billing_cycle' => $subscription->billing_cycle,
            'billing_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);
    }

    public function markPaid(TenantBillingRecord $record, $performedBy = null, ?string $ipAddress = null): TenantBillingRecord
    {
        return DB::transaction(function () use ($record, $performedBy, $ipAddress): TenantBillingRecord {
            $oldValues = $record->toArray();
            $record = $this->billing->update($record, [
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->audit->log('billing.paid', $record->school_id, 'Billing record marked paid.', $oldValues, $record->toArray(), $performedBy, $ipAddress);

            return $record;
        });
    }

    public function markFailed(TenantBillingRecord $record, $performedBy = null, ?string $ipAddress = null): TenantBillingRecord
    {
        return DB::transaction(function () use ($record, $performedBy, $ipAddress): TenantBillingRecord {
            $oldValues = $record->toArray();
            $record = $this->billing->update($record, [
                'status' => 'failed',
            ]);

            $this->audit->log('billing.failed', $record->school_id, 'Billing record marked failed.', $oldValues, $record->toArray(), $performedBy, $ipAddress);

            return $record;
        });
    }

    public function preparePaymentGatewayPayload(TenantBillingRecord $record): array
    {
        return [
            'invoice_no' => $record->invoice_no,
            'amount' => (float) $record->amount,
            'currency' => $record->currency,
            'tenant_id' => $record->school_id,
            'billing_record_id' => $record->id,
        ];
    }

    protected function generateInvoiceNumber(int $tenantId): string
    {
        return sprintf('INV-%d-%s', $tenantId, now()->format('YmdHis'));
    }
}
