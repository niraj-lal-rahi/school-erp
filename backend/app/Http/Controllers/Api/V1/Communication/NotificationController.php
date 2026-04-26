<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\AnnouncementRecipient;
use App\Models\Communication\CommunicationMessage;
use App\Models\Communication\NotificationLog;
use App\Services\Communication\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->notifications->paginate(
                $request->only(['search', 'status', 'channel', 'recipient_type', 'recipient_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function show(NotificationLog $notification): JsonResponse
    {
        return response()->json([
            'data' => $this->notifications->findOrFail($notification->id),
        ]);
    }

    public function markRead(NotificationLog $notification): JsonResponse
    {
        $notification = $this->notifications->markAsRead($notification);

        return response()->json([
            'message' => 'Notification marked as read successfully.',
            'data' => $notification,
        ]);
    }

    public function notificationDeliveryReport(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;
        $baseQuery = NotificationLog::query()
            ->where('school_id', $schoolId)
            ->when($request->filled('channel'), fn ($query) => $query->where('channel', $request->string('channel')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->string('date_from')->toString()))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->string('date_to')->toString()));

        return response()->json([
            'data' => [
                'totals' => [
                    'total' => (clone $baseQuery)->count(),
                    'sent' => (clone $baseQuery)->where('status', 'sent')->count(),
                    'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
                    'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
                    'read' => (clone $baseQuery)->where('status', 'read')->count(),
                ],
                'by_channel' => (clone $baseQuery)
                    ->select('channel', DB::raw('count(*) as total'))
                    ->groupBy('channel')
                    ->get(),
            ],
        ]);
    }

    public function announcementEngagementReport(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;
        $query = AnnouncementRecipient::query()
            ->where('school_id', $schoolId)
            ->with('announcement')
            ->when($request->filled('announcement_id'), fn ($builder) => $builder->where('announcement_id', $request->integer('announcement_id')));

        return response()->json([
            'data' => [
                'total_recipients' => (clone $query)->count(),
                'read_count' => (clone $query)->whereNotNull('read_at')->count(),
                'acknowledged_count' => (clone $query)->whereNotNull('acknowledged_at')->count(),
                'announcements' => (clone $query)
                    ->select('announcement_id', DB::raw('count(*) as total_recipients'), DB::raw('sum(case when read_at is not null then 1 else 0 end) as read_count'), DB::raw('sum(case when acknowledged_at is not null then 1 else 0 end) as acknowledged_count'))
                    ->groupBy('announcement_id')
                    ->get(),
            ],
        ]);
    }

    public function messageVolumeReport(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;
        $query = CommunicationMessage::query()
            ->where('school_id', $schoolId)
            ->when($request->filled('date_from'), fn ($builder) => $builder->whereDate('created_at', '>=', $request->string('date_from')->toString()))
            ->when($request->filled('date_to'), fn ($builder) => $builder->whereDate('created_at', '<=', $request->string('date_to')->toString()));

        return response()->json([
            'data' => [
                'total_messages' => (clone $query)->count(),
                'by_type' => (clone $query)
                    ->select('message_type', DB::raw('count(*) as total'))
                    ->groupBy('message_type')
                    ->get(),
                'by_status' => (clone $query)
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get(),
                'daily_volume' => (clone $query)
                    ->selectRaw('date(created_at) as activity_date, count(*) as total')
                    ->groupBy(DB::raw('date(created_at)'))
                    ->orderBy('activity_date')
                    ->get(),
            ],
        ]);
    }
}
