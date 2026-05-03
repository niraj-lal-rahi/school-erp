<?php

namespace App\Http\Middleware;

use App\Services\Settings\FeatureFlagService;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureFlag
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected FeatureFlagService $features,
    ) {
    }

    public function handle(Request $request, Closure $next, string $featureCode, ?string $module = null): Response
    {
        $allowed = $this->features->checkFeatureEnabled(
            $featureCode,
            $module ?? $featureCode,
            $this->tenantContext->id()
        );

        abort_if(! $allowed, 403, 'This feature is disabled for the current tenant.');

        return $next($request);
    }
}
