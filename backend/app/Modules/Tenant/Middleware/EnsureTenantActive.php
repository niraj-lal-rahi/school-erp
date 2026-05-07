<?php

namespace App\Modules\Tenant\Middleware;

use App\Modules\Tenant\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function __construct(
        protected TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantContext->currentTenant();

        abort_if(! $tenant, 400, 'Tenant context is not resolved.');
        abort_if(in_array($tenant->status, ['suspended', 'cancelled', 'expired'], true), 403, 'Tenant is not active.');
        abort_if(! in_array($tenant->status, ['trial', 'active'], true), 403, 'Tenant is not active.');

        return $next($request);
    }
}
