<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\HR\StaffAttendanceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreStaffAttendanceRequest;
use App\Http\Requests\HR\UpdateStaffAttendanceRequest;
use App\Http\Resources\HR\StaffAttendanceResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use App\Services\HR\StaffAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function __construct(
        protected StaffAttendanceService $attendance,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json(
            StaffAttendanceResource::collection($this->attendance->paginate(
                filters: $request->only([
                    'search',
                    'staff_id',
                    'attendance_status',
                    'attendance_status_type_id',
                    'source',
                    'date_from',
                    'date_to',
                ]),
                perPage: (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(StoreStaffAttendanceRequest $request): JsonResponse
    {
        $staff = Staff::query()->findOrFail((int) $request->validated('staff_id'));
        $this->authorize('update', $staff);

        $attendance = $this->attendance->create(
            $staff,
            StaffAttendanceData::fromArray([
                ...$request->validated(),
                'source' => $request->validated('source') ?? 'manual',
            ]),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Staff attendance recorded successfully.',
            'data' => new StaffAttendanceResource($attendance),
        ], 201);
    }

    public function show(StaffAttendance $staffRecord): JsonResponse
    {
        $this->authorize('view', $staffRecord->staff);

        return response()->json([
            'data' => new StaffAttendanceResource($this->attendance->show($staffRecord)),
        ]);
    }

    public function update(UpdateStaffAttendanceRequest $request, StaffAttendance $staffRecord): JsonResponse
    {
        $this->authorize('update', $staffRecord->staff);

        $attendance = $this->attendance->update(
            $staffRecord,
            StaffAttendanceData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Staff attendance updated successfully.',
            'data' => new StaffAttendanceResource($attendance),
        ]);
    }

    public function destroy(StaffAttendance $staffRecord): JsonResponse
    {
        $this->authorize('update', $staffRecord->staff);
        $this->attendance->delete($staffRecord);

        return response()->json(null, 204);
    }

    public function mark(StoreStaffAttendanceRequest $request, Staff $staff): JsonResponse
    {
        $this->authorize('update', $staff);

        $attendance = $this->attendance->create(
            $staff,
            StaffAttendanceData::fromArray([
                ...$request->validated(),
                'source' => $request->validated('source') ?? 'manual',
            ]),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Staff attendance recorded successfully.',
            'data' => new StaffAttendanceResource($attendance),
        ], 201);
    }
}
