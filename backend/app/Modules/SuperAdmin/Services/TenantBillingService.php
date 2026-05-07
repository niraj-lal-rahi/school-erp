<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantBillingRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantBillingService
{
    protected string $platformConnection = 'platform';

    /**
     * @return Collection<int, TenantBillingRecord>
     */
    public function listByTenant(PlatformTenant $tenant): Collection
    {
        return TenantBillingRecord::query()
            ->with('subscription.subscriptionPlan')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('billing_date')
            ->get();
    }

    public function findOrFail(int $id): TenantBillingRecord
    {
        return TenantBillingRecord::query()
            ->with('subscription.subscriptionPlan')
            ->findOrFail($id);
    }

    public function markPaid(
        TenantBillingRecord $record,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantBillingRecord {
        return DB::connection($this->platformConnection)->transaction(function () use ($record, $performedByUserId, $ipAddress, $userAgent): TenantBillingRecord {
            $record->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->logAction($record->tenant_id, 'tenant_billing_marked_paid', 'tenant_billing', 'Tenant billing record marked paid.', [
                'billing_record_id' => $record->id,
                'invoice_no' => $record->invoice_no,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $record->fresh(['subscription.subscriptionPlan']);
        });
    }

    public function markFailed(
        TenantBillingRecord $record,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantBillingRecord {
        return DB::connection($this->platformConnection)->transaction(function () use ($record, $performedByUserId, $ipAddress, $userAgent): TenantBillingRecord {
            $record->update([
                'status' => 'failed',
                'paid_at' => null,
            ]);

            $this->logAction($record->tenant_id, 'tenant_billing_marked_failed', 'tenant_billing', 'Tenant billing record marked failed.', [
                'billing_record_id' => $record->id,
                'invoice_no' => $record->invoice_no,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $record->fresh(['subscription.subscriptionPlan']);
        });
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        int $tenantId,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $performedByUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
