<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\AttendanceSummaryResource;
use App\Models\Attendance\AttendanceSummary;
use App\Services\Attendance\AttendanceSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSummaryController extends Controller
{
    public function __construct(
        protected AttendanceSummaryService $summaries,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSummary::class);

        return response()->json([
            'data' => AttendanceSummaryResource::collection(
                $this->summaries->all($request->only(['user_type', 'user_id', 'academic_year_id']))
            ),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSummary::class);

        return response()->json([
            'data' => AttendanceSummaryResource::collection(
                $this->summaries->refresh([
                    ...$request->only(['user_type', 'user_id', 'academic_year_id']),
                    'school_id' => $request->user()->school_id,
                ])
            ),
        ]);
    }
}
