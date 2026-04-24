<?php

namespace App\Http\Controllers\Api\V1\HR;

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
            'message' => 'Attendance recorded successfully.',
            'data' => new StaffAttendanceResource($attendance),
        ], 201);
    }

    public function show(StaffAttendance $staffAttendance): JsonResponse
    {
        $this->authorize('view', $staffAttendance->staff);

        return response()->json([
            'data' => new StaffAttendanceResource($this->attendance->show($staffAttendance)),
        ]);
    }

    public function update(UpdateStaffAttendanceRequest $request, StaffAttendance $staffAttendance): JsonResponse
    {
        $attendance = $this->attendance->update(
            $staffAttendance,
            StaffAttendanceData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Attendance updated successfully.',
            'data' => new StaffAttendanceResource($attendance),
        ]);
    }

    public function destroy(StaffAttendance $staffAttendance): JsonResponse
    {
        $this->authorize('update', $staffAttendance->staff);
        $this->attendance->delete($staffAttendance);

        return response()->json(null, 204);
    }

    public function storeForStaff(StoreStaffAttendanceRequest $request, Staff $staff): JsonResponse
    {
        $attendance = $this->attendance->create(
            $staff,
            StaffAttendanceData::fromArray([
                ...$request->validated(),
                'source' => $request->validated('source') ?? 'manual',
            ]),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Attendance recorded successfully.',
            'data' => new StaffAttendanceResource($attendance),
        ], 201);
    }

    public function staffAttendance(Request $request, Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffAttendanceResource::collection($this->attendance->allForStaff(
                $staff,
                $request->only(['date_from', 'date_to', 'attendance_status'])
            )),
        ]);
    }
}
