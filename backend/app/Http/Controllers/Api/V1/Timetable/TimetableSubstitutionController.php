<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\DataTransferObjects\Timetable\TimetableSubstitutionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\UpsertTimetableSubstitutionRequest;
use App\Http\Resources\Timetable\TimetableSubstitutionResource;
use App\Models\Timetable\TimetableSubstitution;
use App\Services\Timetable\TimetableSubstitutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableSubstitutionController extends Controller
{
    public function __construct(
        protected TimetableSubstitutionService $substitutions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TimetableSubstitution::class);

        return response()->json([
            'data' => TimetableSubstitutionResource::collection(
                $this->substitutions->all($request->only([
                    'search',
                    'academic_year_id',
                    'school_class_id',
                    'section_id',
                    'staff_id',
                    'room_id',
                    'status',
                    'substitution_date',
                ]))
            ),
        ]);
    }

    public function store(UpsertTimetableSubstitutionRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableSubstitution::class);

        $substitution = $this->substitutions->create(TimetableSubstitutionData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'status' => 'planned',
            'approved_by' => null,
        ]));

        return response()->json([
            'message' => 'Timetable substitution created successfully.',
            'data' => new TimetableSubstitutionResource($substitution),
        ], 201);
    }

    public function show(TimetableSubstitution $substitution): JsonResponse
    {
        $this->authorize('view', $substitution);

        return response()->json([
            'data' => new TimetableSubstitutionResource($substitution->load([
                'timetableEntry.timetableVersion',
                'timetableEntry.schoolClass',
                'timetableEntry.section',
                'timetableEntry.period',
                'timetableEntry.subject',
                'timetableEntry.room',
                'originalStaff',
                'substituteStaff',
                'approver',
            ])),
        ]);
    }

    public function update(UpsertTimetableSubstitutionRequest $request, TimetableSubstitution $substitution): JsonResponse
    {
        $this->authorize('update', $substitution);

        $substitution = $this->substitutions->update($substitution, TimetableSubstitutionData::fromArray([
            ...$request->validated(),
            'school_id' => $substitution->school_id,
            'status' => $substitution->status,
            'approved_by' => $substitution->approved_by,
        ]));

        return response()->json([
            'message' => 'Timetable substitution updated successfully.',
            'data' => new TimetableSubstitutionResource($substitution),
        ]);
    }

    public function destroy(TimetableSubstitution $substitution): JsonResponse
    {
        $this->authorize('delete', $substitution);
        $this->substitutions->delete($substitution);

        return response()->json(null, 204);
    }

    public function approve(Request $request, TimetableSubstitution $substitution): JsonResponse
    {
        $this->authorize('update', $substitution);

        $substitution = $this->substitutions->approve($substitution, $request->user()->id);

        return response()->json([
            'message' => 'Timetable substitution approved successfully.',
            'data' => new TimetableSubstitutionResource($substitution),
        ]);
    }

    public function cancel(TimetableSubstitution $substitution): JsonResponse
    {
        $this->authorize('update', $substitution);

        $substitution = $this->substitutions->cancel($substitution);

        return response()->json([
            'message' => 'Timetable substitution cancelled successfully.',
            'data' => new TimetableSubstitutionResource($substitution),
        ]);
    }
}
