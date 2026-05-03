<?php

namespace App\Http\Middleware;

use App\Services\Platform\TenantConnectionManager;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePlatformTenant
{
    public function __construct(
        protected TenantConnectionManager $connections,
        protected TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->connections->resolveTenant($request);
        abort_if(! $tenant, 404, 'Platform tenant not found.');

        $this->tenantContext->setTenant($tenant);
        $request->attributes->set('platformTenant', $tenant);

        return $next($request);
    }
}
