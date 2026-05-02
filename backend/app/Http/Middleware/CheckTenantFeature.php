<?php

namespace App\Http\Middleware;

use App\Models\Saas\Tenant;
use App\Services\Saas\TenantFeatureService;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantFeature
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected TenantFeatureService $features,
    ) {
    }

    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        $tenant = $this->tenantContext->get();

        abort_if(! $tenant, 400, 'Tenant context is not resolved.');

        $allowed = $this->features->checkFeatureAccess(
            Tenant::query()->findOrFail($tenant->id),
            $featureCode
        );

        abort_if(! $allowed, 403, 'This feature is not available for the current tenant.');

        return $next($request);
    }
}
