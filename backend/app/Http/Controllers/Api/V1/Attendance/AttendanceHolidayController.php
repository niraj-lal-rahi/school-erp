<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\AttendanceHolidayData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\UpsertAttendanceHolidayRequest;
use App\Http\Resources\Attendance\AttendanceHolidayResource;
use App\Models\Attendance\AttendanceHoliday;
use App\Services\Attendance\AttendanceHolidayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceHolidayController extends Controller
{
    public function __construct(
        protected AttendanceHolidayService $holidays,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceHoliday::class);

        return response()->json([
            'data' => AttendanceHolidayResource::collection(
                $this->holidays->all($request->only(['academic_year_id', 'class_id', 'section_id', 'applies_to', 'search', 'date_from', 'date_to']))
            ),
        ]);
    }

    public function store(UpsertAttendanceHolidayRequest $request): JsonResponse
    {
        $holiday = $this->holidays->create(AttendanceHolidayData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'is_recurring' => $request->boolean('is_recurring'),
        ]));

        return response()->json([
            'message' => 'Attendance holiday created successfully.',
            'data' => new AttendanceHolidayResource($holiday),
        ], 201);
    }

    public function show(AttendanceHoliday $holiday): JsonResponse
    {
        $this->authorize('view', $holiday);

        return response()->json([
            'data' => new AttendanceHolidayResource($holiday->load(['academicYear', 'schoolClass', 'section'])),
        ]);
    }

    public function update(UpsertAttendanceHolidayRequest $request, AttendanceHoliday $holiday): JsonResponse
    {
        $this->authorize('update', $holiday);

        $holiday = $this->holidays->update($holiday, AttendanceHolidayData::fromArray([
            ...$request->validated(),
            'school_id' => $holiday->school_id,
            'is_recurring' => $request->boolean('is_recurring'),
        ]));

        return response()->json([
            'message' => 'Attendance holiday updated successfully.',
            'data' => new AttendanceHolidayResource($holiday),
        ]);
    }

    public function destroy(AttendanceHoliday $holiday): JsonResponse
    {
        $this->authorize('delete', $holiday);
        $this->holidays->delete($holiday);

        return response()->json(null, 204);
    }
}
