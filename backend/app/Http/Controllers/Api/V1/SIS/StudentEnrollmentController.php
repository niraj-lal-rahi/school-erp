<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\EnrollmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpsertStudentEnrollmentRequest;
use App\Http\Resources\SIS\StudentEnrollmentResource;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\SIS\StudentEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentEnrollmentController extends Controller
{
    public function __construct(
        protected StudentEnrollmentService $enrollments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            StudentEnrollmentResource::collection($this->enrollments->paginate(
                $request->only(['search', 'student_id', 'academic_year_id', 'school_class_id', 'section_id', 'status']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertStudentEnrollmentRequest $request): JsonResponse
    {
        $enrollment = $this->enrollments->create(EnrollmentData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Enrollment created successfully.',
            'data' => new StudentEnrollmentResource($enrollment),
        ], 201);
    }

    public function show(StudentEnrollment $studentEnrollment): JsonResponse
    {
        return response()->json([
            'data' => new StudentEnrollmentResource($this->enrollments->show($studentEnrollment)),
        ]);
    }

    public function update(UpsertStudentEnrollmentRequest $request, StudentEnrollment $studentEnrollment): JsonResponse
    {
        $enrollment = $this->enrollments->update($studentEnrollment, EnrollmentData::fromArray([
            ...$studentEnrollment->toArray(),
            ...$request->validated(),
        ]));

        return response()->json([
            'message' => 'Enrollment updated successfully.',
            'data' => new StudentEnrollmentResource($enrollment),
        ]);
    }

    public function destroy(StudentEnrollment $studentEnrollment): JsonResponse
    {
        $this->enrollments->delete($studentEnrollment);

        return response()->json(null, 204);
    }

    public function enroll(UpsertStudentEnrollmentRequest $request, Student $student): JsonResponse
    {
        $enrollment = $this->enrollments->enrollStudent($student, $request->validated());

        return response()->json([
            'message' => 'Student enrolled successfully.',
            'data' => new StudentEnrollmentResource($enrollment),
        ], 201);
    }

    public function studentEnrollments(Student $student): JsonResponse
    {
        return response()->json([
            'data' => StudentEnrollmentResource::collection($this->enrollments->allForStudent($student)),
        ]);
    }
}
