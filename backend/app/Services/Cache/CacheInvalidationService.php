<?php

namespace App\Services\Cache;

class CacheInvalidationService
{
    public function __construct(
        protected TenantCacheService $cache,
    ) {
    }

    public function setting(?int $schoolId, ?string $key = null): void
    {
        if ($key !== null) {
            $this->cache->forget('settings', $schoolId, ['key', $key]);
        }

        $this->cache->flushArea('settings', $schoolId);
        $this->cache->flushArea('settings-public', $schoolId);
        $this->cache->flushArea('public-config', $schoolId);
    }

    public function featureFlags(?int $schoolId, ?string $module = null, ?string $featureCode = null): void
    {
        if ($module !== null && $featureCode !== null) {
            $this->cache->forget('feature-flags', $schoolId, [$module, $featureCode]);
        }

        $this->cache->flushArea('feature-flags', $schoolId);
        $this->cache->flushArea('public-config', $schoolId);
    }

    public function branding(int $schoolId): void
    {
        $this->cache->flushArea('branding', $schoolId);
        $this->cache->flushArea('public-config', $schoolId);
    }

    public function localization(int $schoolId): void
    {
        $this->cache->flushArea('localization', $schoolId);
        $this->cache->flushArea('public-config', $schoolId);
    }

    public function security(?int $schoolId): void
    {
        $this->cache->flushArea('security', $schoolId);
    }

    public function integration(?int $schoolId): void
    {
        $this->cache->flushArea('integrations', $schoolId);
    }

    public function permissions(int $userId, ?int $schoolId = null): void
    {
        $this->cache->forget('rbac-roles', $schoolId, ['user', $userId]);
        $this->cache->forget('rbac-permissions', $schoolId, ['user', $userId]);
    }

    public function dashboard(?int $schoolId, ?int $userId = null): void
    {
        if ($userId !== null) {
            $this->cache->forget('dashboard-widgets', $schoolId, ['user', $userId]);
        }

        $this->cache->flushArea('dashboard-overview', $schoolId);
        $this->cache->flushArea('dashboard-widgets', $schoolId);
    }

    public function academicStructure(?int $schoolId): void
    {
        $this->cache->flushArea('academic-years', $schoolId);
        $this->cache->flushArea('school-classes', $schoolId);
        $this->cache->flushArea('sections', $schoolId);
        $this->cache->flushArea('static-dropdowns', $schoolId);
    }

    public function tenantFeatures(int $schoolId): void
    {
        $this->cache->flushArea('tenant-features', $schoolId);
    }

    public function subscriptionPlanFeatures(?int $planId = null): void
    {
        if ($planId !== null) {
            $this->cache->forget('subscription-plan-features', null, ['plan', $planId]);
        }

        $this->cache->flushArea('subscription-plan-features', null);
    }
}
