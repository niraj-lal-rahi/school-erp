<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\TenantDomain;
use App\Repositories\Contracts\Saas\TenantDomainRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TenantDomainRepository implements TenantDomainRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TenantDomain
    {
        return $this->baseQuery()->withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): TenantDomain
    {
        $domain = TenantDomain::query()->create($attributes);

        return $this->findOrFail($domain->id);
    }

    public function update(TenantDomain $domain, array $attributes): TenantDomain
    {
        $domain->update($attributes);

        return $this->findOrFail($domain->id);
    }

    public function delete(TenantDomain $domain): void
    {
        $domain->delete();
    }

    public function listByTenant(int $schoolId): Collection
    {
        return $this->baseQuery()
            ->where('school_id', $schoolId)
            ->latest('id')
            ->get();
    }

    public function findVerifiedByDomain(string $domain): ?TenantDomain
    {
        return TenantDomain::query()
            ->where('domain', $domain)
            ->where('status', 'active')
            ->where('is_verified', true)
            ->first();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['tenant_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when(array_key_exists('is_verified', $filters), fn (Builder $query) => $query->where('is_verified', (bool) $filters['is_verified']))
            ->when($filters['domain_verification_status'] ?? null, function (Builder $query, string $value): void {
                $query->where('is_verified', $value === 'verified');
            });
    }

    protected function baseQuery(): Builder
    {
        return TenantDomain::query()
            ->with(['tenant']);
    }
}
