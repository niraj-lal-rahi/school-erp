<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Resources\TenantBackupLogResource;
use App\Modules\SuperAdmin\Services\TenantBackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformTenantBackupController extends Controller
{
    public function __construct(
        protected TenantBackupService $backups,
    ) {
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);

        $logs = $this->backups->triggerManualBackup(
            tenant: $tenant,
            requestedByUserId: $request->user()?->id,
            requestedTypes: $this->normalizeBackupTypes($request->input('backup_types')),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant backup queued successfully.',
            'data' => TenantBackupLogResource::collection($logs),
        ], 202);
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);
        $logs = $this->backups->listBackups($tenant, [
            'status' => $request->string('status')->toString() ?: null,
            'backup_type' => $request->string('backup_type')->toString() ?: null,
        ]);

        return response()->json([
            'data' => TenantBackupLogResource::collection($logs),
        ]);
    }

    /**
     * @return list<string>
     */
    protected function normalizeBackupTypes(mixed $types): array
    {
        if (! is_array($types)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $types)));
    }
}
