<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\TenantSubscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TenantSubscriptionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TenantSubscription;

    public function create(array $attributes): TenantSubscription;

    public function update(TenantSubscription $subscription, array $attributes): TenantSubscription;

    public function delete(TenantSubscription $subscription): void;

    public function findActiveForTenant(int $schoolId): ?TenantSubscription;

    public function listByTenant(int $schoolId): Collection;
}
