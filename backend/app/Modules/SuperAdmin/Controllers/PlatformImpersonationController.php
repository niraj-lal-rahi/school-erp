<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Requests\StartImpersonationRequest;
use App\Modules\SuperAdmin\Services\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformImpersonationController extends Controller
{
    public function __construct(
        protected ImpersonationService $impersonations,
    ) {
    }

    public function start(StartImpersonationRequest $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);

        $data = $this->impersonations->start(
            tenant: $tenant,
            platformAdmin: $request->user(),
            reason: (string) $request->validated('reason'),
            emergencyAccess: (bool) $request->boolean('emergency_access', false),
            emergencyAccessLogId: $request->filled('emergency_access_log_id') ? (int) $request->integer('emergency_access_log_id') : null,
            ttlMinutes: $request->filled('ttl_minutes') ? (int) $request->integer('ttl_minutes') : null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant impersonation started successfully.',
            'data' => $data,
        ]);
    }

    public function stop(Request $request): JsonResponse
    {
        $impersonationContext = $request->attributes->get('impersonationContext');
        $impersonationLogId = is_array($impersonationContext) ? (int) ($impersonationContext['id'] ?? 0) : 0;

        abort_if($impersonationLogId <= 0, 400, 'No active impersonation session found.');

        $log = $this->impersonations->stop(
            actingUser: $request->user(),
            impersonationLogId: $impersonationLogId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Impersonation stopped successfully.',
            'data' => [
                'id' => $log->id,
                'status' => $log->status,
                'ended_at' => $log->ended_at?->toIso8601String(),
            ],
        ]);
    }
}
