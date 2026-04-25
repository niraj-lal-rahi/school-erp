<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\DataTransferObjects\Attendance\AttendancePeriodData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\UpsertTimetablePeriodRequest;
use App\Http\Resources\Timetable\TimetablePeriodResource;
use App\Models\Attendance\AttendancePeriod;
use App\Services\Timetable\TimetablePeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetablePeriodController extends Controller
{
    public function __construct(
        protected TimetablePeriodService $periods,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendancePeriod::class);

        return response()->json([
            'data' => TimetablePeriodResource::collection(
                $this->periods->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertTimetablePeriodRequest $request): JsonResponse
    {
        $this->authorize('create', AttendancePeriod::class);

        $period = $this->periods->create(AttendancePeriodData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Timetable period created successfully.',
            'data' => new TimetablePeriodResource($period),
        ], 201);
    }

    public function show(AttendancePeriod $period): JsonResponse
    {
        $this->authorize('view', $period);

        return response()->json([
            'data' => new TimetablePeriodResource($period),
        ]);
    }

    public function update(UpsertTimetablePeriodRequest $request, AttendancePeriod $period): JsonResponse
    {
        $this->authorize('update', $period);

        $period = $this->periods->update($period, AttendancePeriodData::fromArray([
            ...$request->validated(),
            'school_id' => $period->school_id,
        ]));

        return response()->json([
            'message' => 'Timetable period updated successfully.',
            'data' => new TimetablePeriodResource($period),
        ]);
    }

    public function destroy(AttendancePeriod $period): JsonResponse
    {
        $this->authorize('delete', $period);
        $this->periods->delete($period);

        return response()->json(null, 204);
    }
}
