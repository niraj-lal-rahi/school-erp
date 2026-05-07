<?php

namespace App\Modules\Tenant\Middleware;

use App\Modules\Tenant\Services\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePlatformTenant
{
    public function __construct(
        protected TenantConnectionManager $tenants,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenants->resolveTenant($request);

        abort_if(! $tenant, 404, 'Tenant not found.');

        $request->attributes->set('platformTenant', $tenant);

        return $next($request);
    }
}
