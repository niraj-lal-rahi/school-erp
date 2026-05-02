<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\TenantBillingRecord;
use App\Repositories\Contracts\Saas\TenantBillingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TenantBillingRepository implements TenantBillingRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TenantBillingRecord
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $attributes): TenantBillingRecord
    {
        $billingRecord = TenantBillingRecord::query()->create($attributes);

        return $this->findOrFail($billingRecord->id);
    }

    public function update(TenantBillingRecord $billingRecord, array $attributes): TenantBillingRecord
    {
        $billingRecord->update($attributes);

        return $this->findOrFail($billingRecord->id);
    }

    public function listByTenant(int $schoolId): Collection
    {
        return $this->baseQuery()
            ->where('school_id', $schoolId)
            ->latest('id')
            ->get();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['billing_cycle'] ?? null, fn (Builder $query, string $value) => $query->where('billing_cycle', $value))
            ->when($filters['plan'] ?? null, function (Builder $query, $value): void {
                $query->whereHas('subscription', function (Builder $subscriptionQuery) use ($value): void {
                    $subscriptionQuery->where('subscription_plan_id', $value);
                });
            })
            ->when($filters['tenant_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('billing_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('billing_date', '<=', $value));
    }

    protected function baseQuery(): Builder
    {
        return TenantBillingRecord::query()
            ->with(['tenant', 'subscription.subscriptionPlan']);
    }
}
