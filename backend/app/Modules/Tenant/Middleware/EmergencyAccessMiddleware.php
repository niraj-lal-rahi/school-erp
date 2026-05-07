<?php

namespace App\Modules\Tenant\Middleware;

use App\Modules\SuperAdmin\Services\EmergencyAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmergencyAccessMiddleware
{
    public function __construct(
        protected EmergencyAccessService $emergencyAccess,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $impersonationContext = $request->attributes->get('impersonationContext');

        if (! is_array($impersonationContext)) {
            return $next($request);
        }

        $tenantId = (int) ($impersonationContext['tenant_id'] ?? 0);
        $platformAdminUserId = (int) ($impersonationContext['platform_admin_user_id'] ?? 0);
        $emergencyAccessLogId = (int) ($impersonationContext['emergency_access_log_id'] ?? 0);

        if ($tenantId <= 0 || $platformAdminUserId <= 0 || $emergencyAccessLogId <= 0) {
            return $next($request);
        }

        $accessLog = $this->emergencyAccess->validateApprovedAccess(
            logId: $emergencyAccessLogId,
            tenantId: $tenantId,
            platformAdminUserId: $platformAdminUserId,
        );

        if (! $accessLog) {
            return $next($request);
        }

        $request->attributes->set('emergencyAccessContext', [
            'id' => $accessLog->id,
            'tenant_id' => $accessLog->tenant_id,
            'approved_by_user_id' => $accessLog->approved_by_user_id,
            'expires_at' => $accessLog->expires_at?->toIso8601String(),
            'reason' => $accessLog->reason,
        ]);

        $response = $next($request);

        $this->emergencyAccess->touchUsage(
            log: $accessLog,
            path: $request->path(),
            method: $request->method(),
            userId: $request->user()?->id,
        );

        return $response;
    }
}
