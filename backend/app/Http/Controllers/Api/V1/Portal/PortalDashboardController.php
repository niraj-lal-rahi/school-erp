<?php

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Portal\PortalDashboardResource;
use App\Models\Portal\PortalUserProfile;
use App\Services\Portal\PortalActivityLogService;
use App\Services\Portal\PortalDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalDashboardController extends Controller
{
    public function __construct(
        protected PortalDashboardService $dashboardService,
        protected PortalActivityLogService $activityLogs,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PortalUserProfile::class);

        $dashboard = $this->dashboardService->build($request->user());

        $this->activityLogs->log($request->user(), 'portal.dashboard_viewed', [
            'student_id' => $dashboard['active_context']['active_student_id'] ?? null,
            'description' => 'Portal dashboard viewed.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => new PortalDashboardResource($dashboard),
        ]);
    }
}
