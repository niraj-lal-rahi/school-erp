<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Requests\ApproveEmergencyAccessRequest;
use App\Modules\SuperAdmin\Requests\RequestEmergencyAccessRequest;
use App\Modules\SuperAdmin\Requests\RevokeEmergencyAccessRequest;
use App\Modules\SuperAdmin\Services\EmergencyAccessService;
use Illuminate\Http\JsonResponse;

class PlatformEmergencyAccessController extends Controller
{
    public function __construct(
        protected EmergencyAccessService $emergencyAccess,
    ) {
    }

    public function request(RequestEmergencyAccessRequest $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);

        $log = $this->emergencyAccess->requestAccess(
            tenant: $tenant,
            requester: $request->user(),
            reason: (string) $request->validated('reason'),
            ttlMinutes: $request->filled('ttl_minutes') ? (int) $request->integer('ttl_minutes') : null,
            requiresSecondAdminApproval: (bool) $request->boolean('requires_second_admin_approval', true),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Emergency access request created successfully.',
            'data' => [
                'id' => $log->id,
                'tenant_id' => $log->tenant_id,
                'status' => $log->action,
                'reason' => $log->reason,
                'expires_at' => $log->expires_at?->toIso8601String(),
                'requires_second_admin_approval' => (bool) data_get($log->metadata, 'requires_second_admin_approval', false),
            ],
        ], 201);
    }

    public function approve(ApproveEmergencyAccessRequest $request, int $id): JsonResponse
    {
        $log = $this->emergencyAccess->approve(
            logId: $id,
            approver: $request->user(),
            remarks: $request->validated('remarks'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Emergency access approved successfully.',
            'data' => [
                'id' => $log->id,
                'tenant_id' => $log->tenant_id,
                'status' => $log->action,
                'expires_at' => $log->expires_at?->toIso8601String(),
                'approved_by_user_id' => $log->approved_by_user_id,
            ],
        ]);
    }

    public function revoke(RevokeEmergencyAccessRequest $request, int $id): JsonResponse
    {
        $log = $this->emergencyAccess->revoke(
            logId: $id,
            revoker: $request->user(),
            remarks: (string) $request->validated('remarks'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Emergency access revoked successfully.',
            'data' => [
                'id' => $log->id,
                'tenant_id' => $log->tenant_id,
                'status' => $log->action,
                'expires_at' => $log->expires_at?->toIso8601String(),
            ],
        ]);
    }
}
