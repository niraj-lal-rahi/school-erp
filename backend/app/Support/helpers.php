<?php

use App\Models\Saas\Tenant;
use App\Services\Saas\TenantFeatureService;
use App\Services\Saas\TenantUsageService;
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
