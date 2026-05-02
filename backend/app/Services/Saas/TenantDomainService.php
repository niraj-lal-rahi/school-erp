<?php

namespace App\Services\Saas;

use App\Models\Saas\Tenant;
use App\Models\Saas\TenantDomain;
use App\Repositories\Contracts\Saas\TenantDomainRepositoryInterface;
use App\Repositories\Contracts\Saas\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantDomainService
{
    public function __construct(
        protected TenantDomainRepositoryInterface $domains,
        protected TenantRepositoryInterface $tenants,
        protected TenantAuditService $audit,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->domains->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): TenantDomain
    {
        return $this->domains->findOrFail($id);
    }

    public function listByTenant(Tenant $tenant): Collection
    {
        return $this->domains->listByTenant($tenant->id);
    }

    public function createDomain(Tenant $tenant, array $attributes, $performedBy = null, ?string $ipAddress = null): TenantDomain
    {
        return DB::transaction(function () use ($tenant, $attributes, $performedBy, $ipAddress): TenantDomain {
            $domain = $this->domains->create([
                'school_id' => $tenant->id,
                'domain' => $attributes['domain'],
                'domain_type' => $attributes['domain_type'],
                'is_verified' => (bool) ($attributes['is_verified'] ?? false),
                'verified_at' => $attributes['verified_at'] ?? null,
                'status' => $attributes['status'] ?? 'active',
            ]);

            if ($domain->domain_type === 'primary') {
                $this->tenants->update($tenant, ['domain' => $domain->domain]);
            }

            if ($domain->domain_type === 'subdomain') {
                $this->tenants->update($tenant, ['subdomain' => $domain->domain]);
            }

            $this->audit->log('tenant.domain.created', $tenant->id, 'Tenant domain created.', [], $domain->toArray(), $performedBy, $ipAddress);

            return $domain;
        });
    }

    public function verifyDomain(TenantDomain $domain, $performedBy = null, ?string $ipAddress = null): TenantDomain
    {
        return DB::transaction(function () use ($domain, $performedBy, $ipAddress): TenantDomain {
            $oldValues = $domain->toArray();
            $domain = $this->domains->update($domain, [
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
            ]);

            $this->audit->log('tenant.domain.verified', $domain->school_id, 'Tenant domain verified.', $oldValues, $domain->toArray(), $performedBy, $ipAddress);

            return $domain;
        });
    }

    public function resolveTenantByDomain(string $domain): ?Tenant
    {
        $tenantDomain = $this->domains->findVerifiedByDomain($domain);

        if ($tenantDomain) {
            return $tenantDomain->tenant()->first();
        }

        return $this->tenants->findByDomain($domain);
    }
}
