<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantFeatureAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantFeatureAccessService
{
    protected string $cachePrefix = 'platform:tenant-features';

    public function __construct(
        protected PlatformFeatureService $globalFeatures,
    ) {
    }

    public function checkAccess(PlatformTenant $tenant, string $featureCode, ?string $module = null): bool
    {
        $module = $module ?? $featureCode;

        return Cache::remember(
            $this->cacheKey($tenant->id, $featureCode, $module),
            now()->addMinutes(15),
            function () use ($tenant, $featureCode, $module): bool {
                if (! $this->globalFeatures->isEnabled($featureCode, $module)) {
                    return false;
                }

                $feature = TenantFeatureAccess::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('feature_code', $featureCode)
                    ->first();

                if (! $feature) {
                    return false;
                }

                return (bool) $feature->is_enabled;
            }
        );
    }

    /**
     * @return Collection<int, TenantFeatureAccess>
     */
    public function listForTenant(PlatformTenant $tenant, array $filters = []): Collection
    {
        return TenantFeatureAccess::query()
            ->where('tenant_id', $tenant->id)
            ->when(! empty($filters['module']), fn ($query) => $query->where('module', $filters['module']))
            ->orderBy('module')
            ->orderBy('feature_code')
            ->get();
    }

    /**
     * @param  list<array<string, mixed>>  $features
     */
    public function syncTenantOverrides(
        PlatformTenant $tenant,
        array $features,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Collection {
        return DB::connection('platform')->transaction(function () use ($tenant, $features, $performedByUserId, $ipAddress, $userAgent): Collection {
            $results = collect();

            foreach ($features as $feature) {
                $results->push(
                    TenantFeatureAccess::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'feature_code' => $feature['feature_code'],
                        ],
                        [
                            'subscription_plan_id' => $feature['subscription_plan_id'] ?? null,
                            'module' => $feature['module'],
                            'is_enabled' => (bool) ($feature['is_enabled'] ?? true),
                            'limit_value' => $feature['limit_value'] ?? null,
                            'access_source' => $feature['access_source'] ?? 'override',
                            'metadata' => $feature['metadata'] ?? null,
                        ]
                    )
                );

                Cache::forget($this->cacheKey($tenant->id, $feature['feature_code'], $feature['module']));
            }

            $this->logAction(
                $tenant->id,
                'tenant_feature_access_synced',
                'tenant_feature',
                'Tenant feature access updated.',
                [
                    'feature_count' => count($features),
                ],
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            return $results;
        });
    }

    public function clearTenantCache(PlatformTenant $tenant): void
    {
        $features = TenantFeatureAccess::query()
            ->where('tenant_id', $tenant->id)
            ->get(['feature_code', 'module']);

        foreach ($features as $feature) {
            Cache::forget($this->cacheKey($tenant->id, $feature->feature_code, $feature->module));
        }
    }

    protected function cacheKey(int $tenantId, string $featureCode, string $module): string
    {
        return sprintf('%s:%d:%s:%s', $this->cachePrefix, $tenantId, $module, $featureCode);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        int $tenantId,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $performedByUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
