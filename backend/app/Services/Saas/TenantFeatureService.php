<?php

namespace App\Services\Saas;

use App\Models\Saas\SubscriptionPlan;
use App\Models\Saas\Tenant;
use App\Repositories\Contracts\Saas\TenantFeatureRepositoryInterface;
use App\Services\Cache\CacheInvalidationService;
use App\Services\Cache\TenantCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantFeatureService
{
    public function __construct(
        protected TenantFeatureRepositoryInterface $features,
        protected TenantUsageService $usage,
        protected TenantAuditService $audit,
        protected TenantCacheService $cache,
        protected CacheInvalidationService $invalidator,
    ) {
    }

    public function checkFeatureAccess(Tenant $tenant, string $featureCode): bool
    {
        return $this->cache->remember(
            'tenant-features',
            $tenant->id,
            ['access', $featureCode],
            now()->addMinutes(15),
            function () use ($tenant, $featureCode): bool {
                $feature = $this->features->findByTenantAndCode($tenant->id, $featureCode);

                if ($feature !== null) {
                    return (bool) $feature->is_enabled;
                }

                return $tenant->hasFeature($featureCode);
            }
        );
    }

    public function enableFeature(Tenant $tenant, array $featureData, $performedBy = null, ?string $ipAddress = null)
    {
        return $this->upsertFeature($tenant, array_merge($featureData, ['is_enabled' => true]), $performedBy, $ipAddress);
    }

    public function disableFeature(Tenant $tenant, array $featureData, $performedBy = null, ?string $ipAddress = null)
    {
        return $this->upsertFeature($tenant, array_merge($featureData, ['is_enabled' => false]), $performedBy, $ipAddress);
    }

    public function applyPlanFeatureLimits(Tenant $tenant, SubscriptionPlan $plan): Collection
    {
        return DB::transaction(function () use ($tenant, $plan): Collection {
            $plan->loadMissing('planFeatures');
            $this->features->deleteByTenant($tenant->id);

            $applied = $plan->planFeatures->map(function ($feature) use ($tenant) {
                return $this->features->create([
                    'school_id' => $tenant->id,
                    'feature_code' => $feature->feature_code,
                    'module' => $feature->module,
                    'is_enabled' => $feature->is_enabled,
                    'limit_value' => $feature->limit_value,
                ]);
            });

            $this->invalidator->tenantFeatures($tenant->id);

            return $applied;
        });
    }

    public function listFeatures(Tenant $tenant, array $filters = []): Collection
    {
        return $this->cache->remember(
            'tenant-features',
            $tenant->id,
            ['list', $filters],
            now()->addMinutes(15),
            fn () => $this->features->listByTenant($tenant->id, $filters)
        );
    }

    protected function upsertFeature(Tenant $tenant, array $featureData, $performedBy = null, ?string $ipAddress = null)
    {
        return DB::transaction(function () use ($tenant, $featureData, $performedBy, $ipAddress) {
            $existing = $this->features->findByTenantAndCode($tenant->id, $featureData['feature_code']);

            $feature = $existing
                ? $this->features->update($existing, [
                    'module' => $featureData['module'],
                    'is_enabled' => (bool) ($featureData['is_enabled'] ?? true),
                    'limit_value' => $featureData['limit_value'] ?? null,
                ])
                : $this->features->create([
                    'school_id' => $tenant->id,
                    'feature_code' => $featureData['feature_code'],
                    'module' => $featureData['module'],
                    'is_enabled' => (bool) ($featureData['is_enabled'] ?? true),
                    'limit_value' => $featureData['limit_value'] ?? null,
                ]);

            $this->invalidator->tenantFeatures($tenant->id);
            $this->audit->log('tenant.feature.updated', $tenant->id, 'Tenant feature access updated.', $existing?->toArray() ?? [], $feature->toArray(), $performedBy, $ipAddress);

            return $feature;
        });
    }
}
