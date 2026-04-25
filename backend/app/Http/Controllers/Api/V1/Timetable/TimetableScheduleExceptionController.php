<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\DataTransferObjects\Timetable\TimetableScheduleExceptionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\UpsertTimetableScheduleExceptionRequest;
use App\Http\Resources\Timetable\TimetableScheduleExceptionResource;
use App\Models\Timetable\TimetableScheduleException;
use App\Services\Timetable\TimetableScheduleExceptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableScheduleExceptionController extends Controller
{
    public function __construct(
        protected TimetableScheduleExceptionService $exceptions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TimetableScheduleException::class);

        return response()->json([
            'data' => TimetableScheduleExceptionResource::collection(
                $this->exceptions->all($request->only([
                    'search',
                    'academic_year_id',
                    'school_class_id',
                    'section_id',
                    'exception_type',
                    'exception_date',
                ]))
            ),
        ]);
    }

    public function store(UpsertTimetableScheduleExceptionRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableScheduleException::class);

        $exception = $this->exceptions->create(TimetableScheduleExceptionData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'affects_attendance' => (bool) $request->validated('affects_attendance', false),
        ]));

        return response()->json([
            'message' => 'Schedule exception created successfully.',
            'data' => new TimetableScheduleExceptionResource($exception),
        ], 201);
    }

    public function show(TimetableScheduleException $exception): JsonResponse
    {
        $this->authorize('view', $exception);

        return response()->json([
            'data' => new TimetableScheduleExceptionResource($exception->load(['academicYear', 'schoolClass', 'section'])),
        ]);
    }

    public function update(UpsertTimetableScheduleExceptionRequest $request, TimetableScheduleException $exception): JsonResponse
    {
        $this->authorize('update', $exception);

        $exception = $this->exceptions->update($exception, TimetableScheduleExceptionData::fromArray([
            ...$request->validated(),
            'school_id' => $exception->school_id,
            'affects_attendance' => (bool) $request->validated('affects_attendance', false),
        ]));

        return response()->json([
            'message' => 'Schedule exception updated successfully.',
            'data' => new TimetableScheduleExceptionResource($exception),
        ]);
    }

    public function destroy(TimetableScheduleException $exception): JsonResponse
    {
        $this->authorize('delete', $exception);
        $this->exceptions->delete($exception);

        return response()->json(null, 204);
    }
}
