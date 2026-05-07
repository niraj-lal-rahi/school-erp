<?php

namespace App\Modules\Tenant\Middleware;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\Tenant\Services\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    public function __construct(
        protected TenantConnectionManager $tenants,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var PlatformTenant|null $tenant */
        $tenant = $request->attributes->get('platformTenant');

        abort_if(! $tenant, 400, 'Tenant context is not resolved.');

        try {
            $this->tenants->connect($tenant);

            return $next($request);
        } catch (\Throwable $exception) {
            PlatformAuditLog::query()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $request->user()?->id,
                'action' => 'tenant_db_connection_failed',
                'module' => 'tenant_database',
                'description' => 'Failed to connect to tenant database.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'connection_name' => optional($tenant->activeDatabaseConnection)->connection_name,
                    'database_name' => optional($tenant->activeDatabaseConnection)->database_name,
                    'error' => $exception->getMessage(),
                ],
            ]);

            abort(503, 'Tenant database is unavailable.');
        } finally {
            $this->tenants->disconnect();
        }
    }
}
