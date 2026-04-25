<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceSummary;
use App\Services\Attendance\AttendanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceReportService $reports,
    ) {
    }

    public function studentSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSummary::class);

        return response()->json([
            'data' => $this->reports->studentSummary([
                ...$request->only(['academic_year_id', 'class_id', 'section_id', 'student_id', 'attendance_status', 'date_from', 'date_to']),
                'school_id' => $request->user()->school_id,
            ]),
        ]);
    }

    public function staffSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSummary::class);

        return response()->json([
            'data' => $this->reports->staffSummary([
                ...$request->only(['staff_id', 'attendance_status', 'date_from', 'date_to']),
                'school_id' => $request->user()->school_id,
            ]),
        ]);
    }

    public function classAttendance(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSummary::class);

        return response()->json([
            'data' => $this->reports->classAttendance([
                ...$request->only(['academic_year_id', 'class_id', 'section_id', 'date_from', 'date_to']),
                'school_id' => $request->user()->school_id,
            ]),
        ]);
    }

    public function defaulters(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceSummary::class);

        return response()->json([
            'data' => $this->reports->defaulters([
                ...$request->only(['academic_year_id', 'threshold']),
                'school_id' => $request->user()->school_id,
            ]),
        ]);
    }
}
