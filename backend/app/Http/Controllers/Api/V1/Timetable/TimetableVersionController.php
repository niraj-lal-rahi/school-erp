<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\DataTransferObjects\Timetable\TimetableVersionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\UpsertTimetableVersionRequest;
use App\Http\Resources\Timetable\TimetableVersionResource;
use App\Models\Timetable\TimetableVersion;
use App\Services\Timetable\TimetableVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableVersionController extends Controller
{
    public function __construct(
        protected TimetableVersionService $versions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TimetableVersion::class);

        return response()->json([
            'data' => TimetableVersionResource::collection(
                $this->versions->all($request->only(['search', 'academic_year_id', 'status']))
            ),
        ]);
    }

    public function store(UpsertTimetableVersionRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableVersion::class);

        $version = $this->versions->create(TimetableVersionData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'published_at' => $request->validated('status') === 'published' ? now() : null,
            'created_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Timetable version created successfully.',
            'data' => new TimetableVersionResource($version),
        ], 201);
    }

    public function show(TimetableVersion $version): JsonResponse
    {
        $this->authorize('view', $version);

        return response()->json([
            'data' => new TimetableVersionResource($version->load(['academicYear', 'creator', 'publishLogs.performer'])),
        ]);
    }

    public function update(UpsertTimetableVersionRequest $request, TimetableVersion $version): JsonResponse
    {
        $this->authorize('update', $version);

        $version = $this->versions->update($version, TimetableVersionData::fromArray([
            ...$request->validated(),
            'school_id' => $version->school_id,
            'published_at' => $version->published_at,
            'created_by' => $version->created_by,
        ]), $request->user()->id);

        return response()->json([
            'message' => 'Timetable version updated successfully.',
            'data' => new TimetableVersionResource($version),
        ]);
    }

    public function destroy(TimetableVersion $version): JsonResponse
    {
        $this->authorize('delete', $version);
        $this->versions->delete($version);

        return response()->json(null, 204);
    }

    public function publish(Request $request, TimetableVersion $version): JsonResponse
    {
        $this->authorize('update', $version);

        $version = $this->versions->publish($version, $request->user()->id, $request->input('remarks'));

        return response()->json([
            'message' => 'Timetable version published successfully.',
            'data' => new TimetableVersionResource($version),
        ]);
    }

    public function archive(Request $request, TimetableVersion $version): JsonResponse
    {
        $this->authorize('update', $version);

        $version = $this->versions->archive($version, $request->user()->id, $request->input('remarks'));

        return response()->json([
            'message' => 'Timetable version archived successfully.',
            'data' => new TimetableVersionResource($version),
        ]);
    }

    public function duplicate(Request $request, TimetableVersion $version): JsonResponse
    {
        $this->authorize('create', TimetableVersion::class);

        $duplicate = $this->versions->duplicate($version, $request->user()->id);

        return response()->json([
            'message' => 'Timetable version duplicated successfully.',
            'data' => new TimetableVersionResource($duplicate),
        ], 201);
    }
}
