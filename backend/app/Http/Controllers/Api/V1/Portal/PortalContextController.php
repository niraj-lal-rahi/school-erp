<?php

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\SwitchPortalContextRequest;
use App\Http\Resources\Portal\PortalContextResource;
use App\Http\Resources\Portal\PortalProfileResource;
use App\Models\Portal\PortalUserProfile;
use App\Services\Portal\PortalActivityLogService;
use App\Services\Portal\PortalAuthContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalContextController extends Controller
{
    public function __construct(
        protected PortalAuthContextService $contextService,
        protected PortalActivityLogService $activityLogs,
    ) {
    }

    public function context(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PortalUserProfile::class);

        $context = $this->contextService->resolve($request->user());

        $this->activityLogs->log($request->user(), 'portal.context_viewed', [
            'student_id' => $context['active_context']['active_student_id'] ?? null,
            'description' => 'Portal context loaded.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => new PortalContextResource($context),
        ]);
    }

    public function switch(SwitchPortalContextRequest $request): JsonResponse
    {
        $this->authorize('switchContext', PortalUserProfile::class);

        $session = $this->contextService->switchContext($request->user(), $request->validated(), [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_info' => [
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'message' => 'Portal context switched successfully.',
            'data' => [
                'session' => $session,
                'context' => new PortalContextResource($this->contextService->resolve($request->user())),
            ],
        ]);
    }

    public function profiles(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PortalUserProfile::class);

        return response()->json([
            'data' => PortalProfileResource::collection($this->contextService->availableProfiles($request->user())),
        ]);
    }

    public function accessibleStudents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PortalUserProfile::class);

        return response()->json([
            'data' => $this->contextService->accessibleStudents($request->user()),
        ]);
    }
}
