<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Resources\PlatformHealthResource;
use App\Modules\SuperAdmin\Services\PlatformMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformHealthController extends Controller
{
    public function __construct(
        protected PlatformMonitoringService $monitoring,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $health = $this->monitoring->platformHealth();
        $this->logAction($request, 'platform_health_viewed', 'platform_monitoring', 'Platform health viewed.');

        return response()->json([
            'data' => new PlatformHealthResource($health),
        ]);
    }

    public function showTenant(Request $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()
            ->with('activeDatabaseConnection')
            ->findOrFail($id);

        $health = $this->monitoring->tenantHealth($tenant);

        $this->logAction(
            $request,
            'tenant_health_viewed',
            'platform_monitoring',
            'Tenant health viewed.',
            ['tenant_id' => $tenant->id],
            $tenant->id,
        );

        return response()->json([
            'data' => new PlatformHealthResource($health),
        ]);
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
