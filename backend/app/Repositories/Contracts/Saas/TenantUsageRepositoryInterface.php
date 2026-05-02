<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\TenantUsageLimit;

interface TenantUsageRepositoryInterface
{
    public function findByTenantOrFail(int $schoolId): TenantUsageLimit;

    public function create(array $attributes): TenantUsageLimit;

    public function update(TenantUsageLimit $usageLimit, array $attributes): TenantUsageLimit;

    public function updateOrCreateForTenant(int $schoolId, array $attributes): TenantUsageLimit;
}
