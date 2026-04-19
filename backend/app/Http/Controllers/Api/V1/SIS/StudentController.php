<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\StoreStudentRequest;
use App\Http\Requests\SIS\UpdateStudentRequest;
use App\Models\Student;
use App\Services\SIS\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $students,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        return response()->json($this->students->paginate(
            filters: $request->only(['search', 'status']),
            perPage: (int) $request->integer('per_page', 15),
        ));
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->students->create(StudentData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Student created successfully.',
            'data' => $student,
        ], 201);
    }

    public function show(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        return response()->json([
            'data' => $student->load(['guardians', 'enrollments.schoolClass', 'enrollments.section', 'admissions', 'documents']),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student = $this->students->update($student, StudentData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Student updated successfully.',
            'data' => $student,
        ]);
    }

    public function destroy(Student $student): JsonResponse
    {
        $this->authorize('delete', $student);
        $this->students->delete($student);

        return response()->json(null, 204);
    }
}
