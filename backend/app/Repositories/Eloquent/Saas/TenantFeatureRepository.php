<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\TenantFeatureAccess;
use App\Repositories\Contracts\Saas\TenantFeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TenantFeatureRepository implements TenantFeatureRepositoryInterface
{
    public function listByTenant(int $schoolId, array $filters = []): Collection
    {
        return $this->query($schoolId, $filters)
            ->orderBy('module')
            ->orderBy('feature_code')
            ->get();
    }

    public function findByTenantAndCode(int $schoolId, string $featureCode): ?TenantFeatureAccess
    {
        return TenantFeatureAccess::query()
            ->where('school_id', $schoolId)
            ->where('feature_code', $featureCode)
            ->first();
    }

    public function create(array $attributes): TenantFeatureAccess
    {
        $featureAccess = TenantFeatureAccess::query()->create($attributes);

        return $this->findByTenantAndCode($featureAccess->school_id, $featureAccess->feature_code);
    }

    public function update(TenantFeatureAccess $featureAccess, array $attributes): TenantFeatureAccess
    {
        $featureAccess->update($attributes);

        return $this->findByTenantAndCode($featureAccess->school_id, $featureAccess->feature_code);
    }

    public function deleteByTenant(int $schoolId): void
    {
        TenantFeatureAccess::query()
            ->where('school_id', $schoolId)
            ->delete();
    }

    protected function query(int $schoolId, array $filters = []): Builder
    {
        return TenantFeatureAccess::query()
            ->with(['tenant'])
            ->where('school_id', $schoolId)
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when(array_key_exists('is_enabled', $filters), fn (Builder $query) => $query->where('is_enabled', (bool) $filters['is_enabled']));
    }
}
