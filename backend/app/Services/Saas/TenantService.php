<?php

namespace App\Services\Saas;

use App\Models\Saas\Tenant;
use App\Repositories\Contracts\Saas\TenantRepositoryInterface;
use App\Services\Platform\PlatformAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        protected TenantRepositoryInterface $tenants,
        protected TenantAuditService $audit,
        protected PlatformAuditService $platformAudit,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tenants->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Tenant
    {
        return $this->tenants->findOrFail($id);
    }

    public function createTenant(array $attributes, $performedBy = null, ?string $ipAddress = null): Tenant
    {
        return DB::transaction(function () use ($attributes, $performedBy, $ipAddress): Tenant {
            $tenant = $this->tenants->create(array_merge([
                'uuid' => (string) Str::uuid(),
                'slug' => Str::slug($attributes['name']),
                'locale' => 'en',
                'storage_disk' => 's3',
                'timezone' => $attributes['timezone'] ?? 'Asia/Kolkata',
                'currency' => $attributes['currency'] ?? 'INR',
                'status' => $attributes['status'] ?? 'trial',
            ], $attributes));

            $this->audit->log(
                'tenant.created',
                $tenant->id,
                'Tenant created.',
                [],
                $tenant->toArray(),
                $performedBy,
                $ipAddress
            );

            $this->platformAudit->logTenantCreated($tenant, $performedBy, [
                'school_id' => $tenant->id,
                'tenant_code' => $tenant->code,
                'tenant_slug' => $tenant->slug,
                'status' => $tenant->status,
            ]);

            return $tenant;
        });
    }

    public function updateTenant(Tenant $tenant, array $attributes, $performedBy = null, ?string $ipAddress = null): Tenant
    {
        return DB::transaction(function () use ($tenant, $attributes, $performedBy, $ipAddress): Tenant {
            $oldValues = $tenant->toArray();
            $tenant = $this->tenants->update($tenant, $attributes);

            $this->audit->log(
                'tenant.updated',
                $tenant->id,
                'Tenant profile updated.',
                $oldValues,
                $tenant->toArray(),
                $performedBy,
                $ipAddress
            );

            return $tenant;
        });
    }

    public function activateTenant(Tenant $tenant, $performedBy = null, ?string $ipAddress = null): Tenant
    {
        $updated = $this->updateTenant($tenant, [
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
        ], $performedBy, $ipAddress);

        $this->platformAudit->logTenantStatusChanged('activated', $updated, $performedBy, [
            'school_id' => $updated->id,
            'tenant_code' => $updated->code,
        ]);

        return $updated;
    }

    public function suspendTenant(Tenant $tenant, $performedBy = null, ?string $ipAddress = null): Tenant
    {
        $updated = $this->updateTenant($tenant, [
            'status' => 'suspended',
            'suspended_at' => now(),
        ], $performedBy, $ipAddress);

        $this->platformAudit->logTenantStatusChanged('suspended', $updated, $performedBy, [
            'school_id' => $updated->id,
            'tenant_code' => $updated->code,
        ]);

        return $updated;
    }

    public function cancelTenant(Tenant $tenant, $performedBy = null, ?string $ipAddress = null): Tenant
    {
        return $this->updateTenant($tenant, [
            'status' => 'cancelled',
        ], $performedBy, $ipAddress);
    }

    public function restoreTenant(int $tenantId, $performedBy = null, ?string $ipAddress = null): Tenant
    {
        return DB::transaction(function () use ($tenantId, $performedBy, $ipAddress): Tenant {
            $tenant = $this->tenants->restore($tenantId);

            $this->audit->log(
                'tenant.restored',
                $tenant->id,
                'Tenant restored.',
                [],
                $tenant->toArray(),
                $performedBy,
                $ipAddress
            );

            return $tenant;
        });
    }
}
