<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\DataTransferObjects\Timetable\TimetableEntryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\BulkTimetableEntryRequest;
use App\Http\Requests\Timetable\CheckTimetableConflictRequest;
use App\Http\Requests\Timetable\UpsertTimetableEntryRequest;
use App\Http\Resources\Timetable\TimetableEntryResource;
use App\Models\Timetable\TimetableEntry;
use App\Services\Timetable\TimetableEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableEntryController extends Controller
{
    public function __construct(
        protected TimetableEntryService $entries,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TimetableEntry::class);

        return response()->json([
            'data' => TimetableEntryResource::collection(
                $this->entries->all($request->only([
                    'search',
                    'academic_year_id',
                    'school_class_id',
                    'section_id',
                    'staff_id',
                    'room_id',
                    'day_of_week',
                    'timetable_version_id',
                    'status',
                ]))
            ),
        ]);
    }

    public function store(UpsertTimetableEntryRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableEntry::class);

        $entry = $this->entries->create(TimetableEntryData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Timetable entry created successfully.',
            'data' => new TimetableEntryResource($entry),
        ], 201);
    }

    public function show(TimetableEntry $entry): JsonResponse
    {
        $this->authorize('view', $entry);

        return response()->json([
            'data' => new TimetableEntryResource($entry->load([
                'timetableVersion',
                'academicYear',
                'schoolClass',
                'section',
                'period',
                'subject',
                'staff',
                'room',
            ])),
        ]);
    }

    public function update(UpsertTimetableEntryRequest $request, TimetableEntry $entry): JsonResponse
    {
        $this->authorize('update', $entry);

        $entry = $this->entries->update($entry, TimetableEntryData::fromArray([
            ...$request->validated(),
            'school_id' => $entry->school_id,
        ]));

        return response()->json([
            'message' => 'Timetable entry updated successfully.',
            'data' => new TimetableEntryResource($entry),
        ]);
    }

    public function destroy(TimetableEntry $entry): JsonResponse
    {
        $this->authorize('delete', $entry);
        $this->entries->delete($entry);

        return response()->json(null, 204);
    }

    public function bulkCreate(BulkTimetableEntryRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableEntry::class);

        $entries = collect($request->validated('entries'))
            ->map(fn (array $entry) => [
                ...$entry,
                'school_id' => $request->user()->school_id,
            ])
            ->all();

        return response()->json([
            'message' => 'Timetable entries created successfully.',
            'data' => TimetableEntryResource::collection($this->entries->bulkCreate($entries)),
        ], 201);
    }

    public function checkConflicts(CheckTimetableConflictRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableEntry::class);

        return response()->json([
            'data' => [
                'has_conflicts' => $this->entries->checkConflicts([
                    ...$request->validated(),
                    'school_id' => $request->user()->school_id,
                ], $request->validated('ignore_entry_id')) !== [],
                'conflicts' => $this->entries->checkConflicts([
                    ...$request->validated(),
                    'school_id' => $request->user()->school_id,
                ], $request->validated('ignore_entry_id')),
            ],
        ]);
    }
}
