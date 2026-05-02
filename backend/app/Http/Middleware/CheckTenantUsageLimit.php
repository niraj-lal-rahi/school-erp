<?php

namespace App\Http\Middleware;

use App\Models\Saas\Tenant;
use App\Services\Saas\TenantUsageService;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantUsageLimit
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected TenantUsageService $usage,
    ) {
    }

    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $tenant = $this->tenantContext->get();

        abort_if(! $tenant, 400, 'Tenant context is not resolved.');

        $isExceeded = $this->usage->isLimitExceeded(
            Tenant::query()->findOrFail($tenant->id),
            $limitType
        );

        abort_if($isExceeded, 403, "The tenant has exceeded the allowed {$limitType} limit.");

        return $next($request);
    }
}
