<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertHomeworkAssignmentRequest;
use App\Http\Resources\AcademicManagement\HomeworkAssignmentResource;
use App\Models\AcademicManagement\HomeworkAssignment;
use App\Services\AcademicManagement\HomeworkAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeworkAssignmentController extends Controller
{
    public function __construct(protected HomeworkAssignmentService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', HomeworkAssignment::class);

        return HomeworkAssignmentResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertHomeworkAssignmentRequest $request): JsonResponse
    {
        $this->authorize('create', HomeworkAssignment::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Assignment created successfully.', 'data' => new HomeworkAssignmentResource($record)], 201);
    }

    public function show(HomeworkAssignment $assignment): HomeworkAssignmentResource
    {
        $this->authorize('view', $assignment);

        return new HomeworkAssignmentResource($this->service->show($assignment->id));
    }

    public function update(UpsertHomeworkAssignmentRequest $request, HomeworkAssignment $assignment): JsonResponse
    {
        $this->authorize('update', $assignment);
        $updated = $this->service->update($assignment, $request->validated());

        return response()->json(['message' => 'Assignment updated successfully.', 'data' => new HomeworkAssignmentResource($updated)]);
    }

    public function destroy(HomeworkAssignment $assignment): JsonResponse
    {
        $this->authorize('delete', $assignment);
        $this->service->delete($assignment);

        return response()->json(null, 204);
    }
}
