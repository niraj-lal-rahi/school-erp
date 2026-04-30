<?php

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Portal\PortalNotificationResource;
use App\Models\Portal\PortalNotification;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalNotificationController extends Controller
{
    public function __construct(
        protected PortalNotificationService $notificationService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PortalNotification::class);

        $notifications = $this->notificationService->list(
            $request->user(),
            $request->only(['student_id', 'notification_type', 'is_read']),
            (int) $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => PortalNotificationResource::collection($notifications->getCollection()),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markRead(Request $request, PortalNotification $notification): JsonResponse
    {
        $this->authorize('markRead', $notification);

        $notification = $this->notificationService->markRead($request->user(), $notification);

        return response()->json([
            'message' => 'Portal notification marked as read successfully.',
            'data' => new PortalNotificationResource($notification),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PortalNotification::class);

        $count = $this->notificationService->markAllRead($request->user());

        return response()->json([
            'message' => 'All portal notifications marked as read successfully.',
            'data' => [
                'updated_count' => $count,
            ],
        ]);
    }
}
