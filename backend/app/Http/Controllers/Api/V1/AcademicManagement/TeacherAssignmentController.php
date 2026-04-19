<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertTeacherAssignmentRequest;
use App\Http\Resources\AcademicManagement\TeacherAssignmentResource;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Services\AcademicManagement\TeacherAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function __construct(protected TeacherAssignmentService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', TeacherAssignment::class);

        return TeacherAssignmentResource::collection($this->service->paginate($request->only(['academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertTeacherAssignmentRequest $request): JsonResponse
    {
        $this->authorize('create', TeacherAssignment::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Teacher assignment created successfully.', 'data' => new TeacherAssignmentResource($record)], 201);
    }

    public function show(TeacherAssignment $teacherAssignment): TeacherAssignmentResource
    {
        $this->authorize('view', $teacherAssignment);

        return new TeacherAssignmentResource($this->service->show($teacherAssignment->id));
    }

    public function update(UpsertTeacherAssignmentRequest $request, TeacherAssignment $teacherAssignment): JsonResponse
    {
        $this->authorize('update', $teacherAssignment);
        $updated = $this->service->update($teacherAssignment, $request->validated());

        return response()->json(['message' => 'Teacher assignment updated successfully.', 'data' => new TeacherAssignmentResource($updated)]);
    }

    public function destroy(TeacherAssignment $teacherAssignment): JsonResponse
    {
        $this->authorize('delete', $teacherAssignment);
        $this->service->delete($teacherAssignment);

        return response()->json(null, 204);
    }
}
