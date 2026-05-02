<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\Tenant;
use App\Repositories\Contracts\Saas\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TenantRepository implements TenantRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Tenant
    {
        return $this->baseQuery()->withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): Tenant
    {
        $tenant = Tenant::query()->create($attributes);

        return $this->findOrFail($tenant->id);
    }

    public function update(Tenant $tenant, array $attributes): Tenant
    {
        $tenant->update($attributes);

        return $this->findOrFail($tenant->id);
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    public function restore(int $id): Tenant
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->restore();

        return $this->findOrFail($tenant->id);
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return Tenant::query()
            ->where('domain', $domain)
            ->orWhere('subdomain', $domain)
            ->orWhereHas('domains', function (Builder $query) use ($domain): void {
                $query->where('domain', $domain);
            })
            ->first();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $tenantQuery) use ($search): void {
                    $tenantQuery->where('schools.name', 'like', "%{$search}%")
                        ->orWhere('schools.code', 'like', "%{$search}%")
                        ->orWhere('schools.email', 'like', "%{$search}%")
                        ->orWhere('schools.phone', 'like', "%{$search}%")
                        ->orWhere('schools.domain', 'like', "%{$search}%")
                        ->orWhere('schools.subdomain', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('schools.status', $value))
            ->when($filters['plan'] ?? null, function (Builder $query, $plan): void {
                $query->whereHas('tenantSubscriptions', function (Builder $subscriptionQuery) use ($plan): void {
                    $subscriptionQuery->where('subscription_plan_id', $plan);
                });
            })
            ->when($filters['trial_expiring'] ?? null, function (Builder $query, $days): void {
                $query->whereNotNull('schools.trial_ends_at')
                    ->whereDate('schools.trial_ends_at', '<=', now()->addDays((int) $days)->toDateString())
                    ->whereDate('schools.trial_ends_at', '>=', now()->toDateString());
            });
    }

    protected function baseQuery(): Builder
    {
        return Tenant::query()
            ->with(['activeSubscription.subscriptionPlan', 'usageLimit'])
            ->withCount(['users', 'students', 'tenantSubscriptions', 'domains']);
    }
}
