<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\AttendancePeriodData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\UpsertAttendancePeriodRequest;
use App\Http\Resources\Attendance\AttendancePeriodResource;
use App\Models\Attendance\AttendancePeriod;
use App\Services\Attendance\AttendancePeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendancePeriodController extends Controller
{
    public function __construct(
        protected AttendancePeriodService $attendancePeriods,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendancePeriod::class);

        return response()->json([
            'data' => AttendancePeriodResource::collection(
                $this->attendancePeriods->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertAttendancePeriodRequest $request): JsonResponse
    {
        $attendancePeriod = $this->attendancePeriods->create(AttendancePeriodData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Attendance period created successfully.',
            'data' => new AttendancePeriodResource($attendancePeriod),
        ], 201);
    }

    public function show(AttendancePeriod $attendancePeriod): JsonResponse
    {
        $this->authorize('view', $attendancePeriod);

        return response()->json([
            'data' => new AttendancePeriodResource($attendancePeriod),
        ]);
    }

    public function update(UpsertAttendancePeriodRequest $request, AttendancePeriod $attendancePeriod): JsonResponse
    {
        $this->authorize('update', $attendancePeriod);

        $attendancePeriod = $this->attendancePeriods->update($attendancePeriod, AttendancePeriodData::fromArray([
            ...$request->validated(),
            'school_id' => $attendancePeriod->school_id,
        ]));

        return response()->json([
            'message' => 'Attendance period updated successfully.',
            'data' => new AttendancePeriodResource($attendancePeriod),
        ]);
    }

    public function destroy(AttendancePeriod $attendancePeriod): JsonResponse
    {
        $this->authorize('delete', $attendancePeriod);
        $this->attendancePeriods->delete($attendancePeriod);

        return response()->json(null, 204);
    }
}
