<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\TenantUsageLimit;
use App\Repositories\Contracts\Saas\TenantUsageRepositoryInterface;

class TenantUsageRepository implements TenantUsageRepositoryInterface
{
    public function findByTenantOrFail(int $schoolId): TenantUsageLimit
    {
        return TenantUsageLimit::query()
            ->with(['tenant', 'subscriptionPlan'])
            ->where('school_id', $schoolId)
            ->firstOrFail();
    }

    public function create(array $attributes): TenantUsageLimit
    {
        $usageLimit = TenantUsageLimit::query()->create($attributes);

        return $this->findByTenantOrFail($usageLimit->school_id);
    }

    public function update(TenantUsageLimit $usageLimit, array $attributes): TenantUsageLimit
    {
        $usageLimit->update($attributes);

        return $this->findByTenantOrFail($usageLimit->school_id);
    }

    public function updateOrCreateForTenant(int $schoolId, array $attributes): TenantUsageLimit
    {
        TenantUsageLimit::query()->updateOrCreate(
            ['school_id' => $schoolId],
            $attributes
        );

        return $this->findByTenantOrFail($schoolId);
    }
}
