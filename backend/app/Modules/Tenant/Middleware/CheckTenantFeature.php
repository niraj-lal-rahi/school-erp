<?php

namespace App\Modules\Tenant\Middleware;

use App\Modules\SuperAdmin\Services\TenantFeatureAccessService;
use App\Modules\Tenant\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantFeature
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected TenantFeatureAccessService $features,
    ) {
    }

    public function handle(Request $request, Closure $next, string $featureCode, ?string $module = null): Response
    {
        $tenant = $this->tenantContext->getTenant();

        abort_if(! $tenant, 400, 'Tenant context is not resolved.');

        $allowed = $this->features->checkAccess($tenant, $featureCode, $module ?? $featureCode);

        abort_if(! $allowed, 403, 'This feature is not available for the current tenant.');

        return $next($request);
    }
}
