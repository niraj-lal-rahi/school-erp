<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Timetable\TimetablePublishLogResource;
use App\Models\Timetable\TimetablePublishLog;
use App\Services\Timetable\TimetablePublishLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetablePublishLogController extends Controller
{
    public function __construct(
        protected TimetablePublishLogService $logs,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TimetablePublishLog::class);

        return response()->json([
            'data' => TimetablePublishLogResource::collection(
                $this->logs->all($request->only(['timetable_version_id', 'action']))
            ),
        ]);
    }
}
