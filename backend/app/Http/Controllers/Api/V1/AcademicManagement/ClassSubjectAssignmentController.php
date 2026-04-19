<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertClassSubjectAssignmentRequest;
use App\Http\Resources\AcademicManagement\ClassSubjectAssignmentResource;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Services\AcademicManagement\ClassSubjectAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassSubjectAssignmentController extends Controller
{
    public function __construct(protected ClassSubjectAssignmentService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ClassSubjectAssignment::class);

        return ClassSubjectAssignmentResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertClassSubjectAssignmentRequest $request): JsonResponse
    {
        $this->authorize('create', ClassSubjectAssignment::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Class subject assignment created successfully.', 'data' => new ClassSubjectAssignmentResource($record)], 201);
    }

    public function show(ClassSubjectAssignment $classSubject): ClassSubjectAssignmentResource
    {
        $this->authorize('view', $classSubject);

        return new ClassSubjectAssignmentResource($this->service->show($classSubject->id));
    }

    public function update(UpsertClassSubjectAssignmentRequest $request, ClassSubjectAssignment $classSubject): JsonResponse
    {
        $this->authorize('update', $classSubject);
        $updated = $this->service->update($classSubject, $request->validated());

        return response()->json(['message' => 'Class subject assignment updated successfully.', 'data' => new ClassSubjectAssignmentResource($updated)]);
    }

    public function destroy(ClassSubjectAssignment $classSubject): JsonResponse
    {
        $this->authorize('delete', $classSubject);
        $this->service->delete($classSubject);

        return response()->json(null, 204);
    }
}
