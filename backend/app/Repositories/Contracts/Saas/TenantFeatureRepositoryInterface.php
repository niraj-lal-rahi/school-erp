<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\TenantFeatureAccess;
use Illuminate\Support\Collection;

interface TenantFeatureRepositoryInterface
{
    public function listByTenant(int $schoolId, array $filters = []): Collection;

    public function findByTenantAndCode(int $schoolId, string $featureCode): ?TenantFeatureAccess;

    public function create(array $attributes): TenantFeatureAccess;

    public function update(TenantFeatureAccess $featureAccess, array $attributes): TenantFeatureAccess;

    public function deleteByTenant(int $schoolId): void;
}
