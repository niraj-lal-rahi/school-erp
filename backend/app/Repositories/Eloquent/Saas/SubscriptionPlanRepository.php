<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\SubscriptionPlan;
use App\Repositories\Contracts\Saas\SubscriptionPlanRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SubscriptionPlanRepository implements SubscriptionPlanRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->query($filters)
            ->latest('id')
            ->get();
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $attributes): SubscriptionPlan
    {
        $plan = SubscriptionPlan::query()->create($attributes);

        return $this->findOrFail($plan->id);
    }

    public function update(SubscriptionPlan $plan, array $attributes): SubscriptionPlan
    {
        $plan->update($attributes);

        return $this->findOrFail($plan->id);
    }

    public function delete(SubscriptionPlan $plan): void
    {
        $plan->delete();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $planQuery) use ($search): void {
                    $planQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value));
    }

    protected function baseQuery(): Builder
    {
        return SubscriptionPlan::query()
            ->withCount(['planFeatures', 'tenantSubscriptions']);
    }
}
