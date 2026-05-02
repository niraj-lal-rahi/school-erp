<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\TenantSubscription;
use App\Repositories\Contracts\Saas\TenantSubscriptionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TenantSubscriptionRepository implements TenantSubscriptionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TenantSubscription
    {
        return $this->baseQuery()->withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): TenantSubscription
    {
        $subscription = TenantSubscription::query()->create($attributes);

        return $this->findOrFail($subscription->id);
    }

    public function update(TenantSubscription $subscription, array $attributes): TenantSubscription
    {
        $subscription->update($attributes);

        return $this->findOrFail($subscription->id);
    }

    public function delete(TenantSubscription $subscription): void
    {
        $subscription->delete();
    }

    public function findActiveForTenant(int $schoolId): ?TenantSubscription
    {
        return $this->baseQuery()
            ->where('school_id', $schoolId)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->latest('start_date')
            ->first();
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
            ->when($filters['subscription_status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['billing_cycle'] ?? null, fn (Builder $query, string $value) => $query->where('billing_cycle', $value))
            ->when($filters['plan'] ?? null, fn (Builder $query, $value) => $query->where('subscription_plan_id', $value))
            ->when($filters['tenant_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('start_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('start_date', '<=', $value))
            ->when($filters['trial_expiring'] ?? null, function (Builder $query, $days): void {
                $query->whereNotNull('trial_ends_at')
                    ->whereDate('trial_ends_at', '<=', now()->addDays((int) $days)->toDateString())
                    ->whereDate('trial_ends_at', '>=', now()->toDateString());
            });
    }

    protected function baseQuery(): Builder
    {
        return TenantSubscription::query()
            ->with(['tenant', 'subscriptionPlan'])
            ->withCount(['billingRecords']);
    }
}
