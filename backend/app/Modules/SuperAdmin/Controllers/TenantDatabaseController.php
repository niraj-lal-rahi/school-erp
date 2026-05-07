<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Resources\TenantDatabaseConnectionResource;
use App\Modules\Tenant\Services\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantDatabaseController extends Controller
{
    public function __construct(
        protected TenantConnectionManager $tenantConnections,
    ) {
    }

    public function test(Request $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()
            ->with('activeDatabaseConnection')
            ->findOrFail($id);

        if (! $tenant->activeDatabaseConnection) {
            return response()->json([
                'message' => 'Tenant does not have an active database connection configured.',
            ], 422);
        }

        $successful = $this->tenantConnections->testConnection($tenant);
        $tenant->load('activeDatabaseConnection');

        $this->logAction(
            $request,
            $successful ? 'platform_tenant_database_tested' : 'platform_tenant_database_test_failed',
            'tenant_database',
            $successful ? 'Tenant database connection test succeeded.' : 'Tenant database connection test failed.',
            [
                'tenant_id' => $tenant->id,
                'connection_status' => $tenant->activeDatabaseConnection?->connection_status,
            ],
            $tenant->id,
        );

        return response()->json([
            'message' => $successful
                ? 'Tenant database connection test passed.'
                : 'Tenant database connection test failed.',
            'data' => [
                'success' => $successful,
                'connection' => new TenantDatabaseConnectionResource($tenant->activeDatabaseConnection),
            ],
        ], $successful ? 200 : 422);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        Request $request,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $tenantId = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
