<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\TenantDomain;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TenantDomainRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TenantDomain;

    public function create(array $attributes): TenantDomain;

    public function update(TenantDomain $domain, array $attributes): TenantDomain;

    public function delete(TenantDomain $domain): void;

    public function listByTenant(int $schoolId): Collection;

    public function findVerifiedByDomain(string $domain): ?TenantDomain;
}
