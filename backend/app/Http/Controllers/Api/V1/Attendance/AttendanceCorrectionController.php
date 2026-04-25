<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\AttendanceCorrectionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ReviewAttendanceCorrectionRequest;
use App\Http\Requests\Attendance\StoreAttendanceCorrectionRequest;
use App\Http\Resources\Attendance\AttendanceCorrectionResource;
use App\Models\Attendance\AttendanceCorrection;
use App\Services\Attendance\AttendanceCorrectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    public function __construct(
        protected AttendanceCorrectionService $corrections,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceCorrection::class);

        return response()->json([
            'data' => AttendanceCorrectionResource::collection(
                $this->corrections->all($request->only(['reference_type', 'reference_id', 'status', 'attendance_date_from', 'attendance_date_to']))
            ),
        ]);
    }

    public function store(StoreAttendanceCorrectionRequest $request): JsonResponse
    {
        $correction = $this->corrections->create(AttendanceCorrectionData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Attendance correction request submitted successfully.',
            'data' => new AttendanceCorrectionResource($correction),
        ], 201);
    }

    public function show(AttendanceCorrection $correction): JsonResponse
    {
        $this->authorize('view', $correction);

        return response()->json([
            'data' => new AttendanceCorrectionResource($correction->load(['oldStatus', 'newStatus', 'approver'])),
        ]);
    }

    public function approve(ReviewAttendanceCorrectionRequest $request, AttendanceCorrection $correction): JsonResponse
    {
        $this->authorize('update', $correction);

        $correction = $this->corrections->approve($correction, $request->input('review_remarks'), $request->user()->id);

        return response()->json([
            'message' => 'Attendance correction approved successfully.',
            'data' => new AttendanceCorrectionResource($correction),
        ]);
    }

    public function reject(ReviewAttendanceCorrectionRequest $request, AttendanceCorrection $correction): JsonResponse
    {
        $this->authorize('update', $correction);

        $correction = $this->corrections->reject($correction, $request->input('review_remarks'), $request->user()->id);

        return response()->json([
            'message' => 'Attendance correction rejected successfully.',
            'data' => new AttendanceCorrectionResource($correction),
        ]);
    }
}
