<?php

use App\Models\Saas\Tenant;
use App\Services\Saas\TenantFeatureService;
use App\Services\Saas\TenantUsageService;
use App\Services\Settings\FeatureFlagService;
use App\Services\Settings\PublicConfigService;
use App\Services\Settings\SettingService;
use App\Support\Multitenancy\TenantContext;

if (! function_exists('currentTenant')) {
    function currentTenant(): ?Tenant
    {
        $tenant = app(TenantContext::class)->get();

        if (! $tenant) {
            return null;
        }

        return Tenant::query()->find($tenant->id);
    }
}

if (! function_exists('tenantHasFeature')) {
    function tenantHasFeature(string $featureCode): bool
    {
        $tenant = currentTenant();

        if (! $tenant) {
            return false;
        }

        return app(TenantFeatureService::class)->checkFeatureAccess($tenant, $featureCode);
    }
}

if (! function_exists('tenantLimitExceeded')) {
    function tenantLimitExceeded(string $limitType): bool
    {
        $tenant = currentTenant();

        if (! $tenant) {
            return false;
        }

        return app(TenantUsageService::class)->isLimitExceeded($tenant, $limitType);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->getByKey($key, null, $default);
    }
}

if (! function_exists('tenantSetting')) {
    function tenantSetting(string $key, mixed $default = null): mixed
    {
        $tenant = currentTenant();

        if (! $tenant) {
            return setting($key, $default);
        }

        return app(SettingService::class)->getByKey($key, $tenant->id, $default);
    }
}

if (! function_exists('featureEnabled')) {
    function featureEnabled(string $featureCode, ?string $module = null): bool
    {
        $tenant = currentTenant();
        $resolvedModule = $module ?? $featureCode;

        return app(FeatureFlagService::class)->checkFeatureEnabled($featureCode, $resolvedModule, $tenant?->id);
    }
}

if (! function_exists('publicConfig')) {
    function publicConfig(): array
    {
        $tenant = currentTenant();

        return app(PublicConfigService::class)->get($tenant?->id);
    }
}
