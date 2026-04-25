<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\AttendanceStatusTypeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\UpsertAttendanceStatusTypeRequest;
use App\Http\Resources\Attendance\AttendanceStatusTypeResource;
use App\Models\Attendance\AttendanceStatusType;
use App\Services\Attendance\AttendanceStatusTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceStatusTypeController extends Controller
{
    public function __construct(
        protected AttendanceStatusTypeService $attendanceStatusTypes,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceStatusType::class);

        return response()->json([
            'data' => AttendanceStatusTypeResource::collection(
                $this->attendanceStatusTypes->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertAttendanceStatusTypeRequest $request): JsonResponse
    {
        $attendanceStatusType = $this->attendanceStatusTypes->create(AttendanceStatusTypeData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Attendance status type created successfully.',
            'data' => new AttendanceStatusTypeResource($attendanceStatusType),
        ], 201);
    }

    public function show(AttendanceStatusType $attendanceStatusType): JsonResponse
    {
        $this->authorize('view', $attendanceStatusType);

        return response()->json([
            'data' => new AttendanceStatusTypeResource($attendanceStatusType),
        ]);
    }

    public function update(UpsertAttendanceStatusTypeRequest $request, AttendanceStatusType $attendanceStatusType): JsonResponse
    {
        $this->authorize('update', $attendanceStatusType);

        $attendanceStatusType = $this->attendanceStatusTypes->update($attendanceStatusType, AttendanceStatusTypeData::fromArray([
            ...$request->validated(),
            'school_id' => $attendanceStatusType->school_id,
        ]));

        return response()->json([
            'message' => 'Attendance status type updated successfully.',
            'data' => new AttendanceStatusTypeResource($attendanceStatusType),
        ]);
    }

    public function destroy(AttendanceStatusType $attendanceStatusType): JsonResponse
    {
        $this->authorize('delete', $attendanceStatusType);
        $this->attendanceStatusTypes->delete($attendanceStatusType);

        return response()->json(null, 204);
    }
}
