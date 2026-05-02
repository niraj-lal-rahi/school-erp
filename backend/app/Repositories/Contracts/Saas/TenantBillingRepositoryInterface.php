<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\TenantBillingRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TenantBillingRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TenantBillingRecord;

    public function create(array $attributes): TenantBillingRecord;

    public function update(TenantBillingRecord $billingRecord, array $attributes): TenantBillingRecord;

    public function listByTenant(int $schoolId): Collection;
}
