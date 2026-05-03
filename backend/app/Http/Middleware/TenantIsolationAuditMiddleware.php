<?php

namespace App\Http\Middleware;

use App\Services\Security\SensitiveActionAuditService;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantIsolationAuditMiddleware
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected SensitiveActionAuditService $audit,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantContext->get();
        $user = $request->user();

        if ($user && $tenant && (int) $user->school_id !== (int) $tenant->id) {
            $this->audit->log('tenant.isolation.mismatch', [
                'resolved_tenant_id' => $tenant->id,
                'user_school_id' => $user->school_id,
                'headers' => [
                    'x_tenant_id' => $request->header('X-Tenant-Id'),
                    'x_tenant_code' => $request->header('X-Tenant-Code'),
                    'x_tenant_domain' => $request->header('X-Tenant-Domain'),
                ],
            ], $request, 'warning');

            abort(403, 'Tenant isolation check failed.');
        }

        return $next($request);
    }
}
