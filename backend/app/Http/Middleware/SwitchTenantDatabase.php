<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Models\Platform\PlatformTenant;
use App\Services\Platform\PlatformAuditService;
use App\Services\Platform\TenantConnectionManager;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SwitchTenantDatabase
{
    public function __construct(
        protected TenantConnectionManager $connections,
        protected TenantContext $tenantContext,
        protected PlatformAuditService $audit,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var PlatformTenant|null $tenant */
        $tenant = $request->attributes->get('platformTenant') ?: $this->tenantContext->getTenant();

        abort_if(! $tenant, 400, 'Platform tenant context is not resolved.');
        abort_if(in_array($tenant->status, ['suspended', 'cancelled', 'expired'], true), 403, 'Tenant is not active.');

        try {
            $this->connections->connect($tenant);
            $this->hydrateTenantSchoolContext($tenant);

            return $next($request);
        } catch (Throwable $exception) {
            $this->logConnectionFailure($tenant, $request, $exception);

            abort(503, 'Unable to establish tenant database connection.');
        } finally {
            $this->connections->disconnect();
        }
    }

    protected function logConnectionFailure(PlatformTenant $tenant, Request $request, Throwable $exception): void
    {
        $this->audit->logDatabaseConnectionFailure(
            $tenant,
            [
                'message' => $exception->getMessage(),
                'tenant_code' => $tenant->code,
                'path' => $request->path(),
            ],
            $request->user(),
            $request,
        );
    }

    protected function hydrateTenantSchoolContext(PlatformTenant $tenant): void
    {
        $school = School::withoutGlobalScopes()->find($tenant->id);

        if ($school) {
            $this->tenantContext->set($school);
        }
    }
}
