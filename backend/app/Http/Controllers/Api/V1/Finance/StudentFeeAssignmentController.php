<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\StudentFeeAssignmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\AssignFeeStructureToStudentRequest;
use App\Http\Requests\Finance\UpsertStudentFeeAssignmentRequest;
use App\Http\Resources\Finance\StudentFeeAssignmentResource;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\Student;
use App\Services\Finance\StudentFeeAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentFeeAssignmentController extends Controller
{
    public function __construct(
        protected StudentFeeAssignmentService $assignments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentFeeAssignment::class);

        return response()->json(
            StudentFeeAssignmentResource::collection($this->assignments->paginate(
                $request->only(['search', 'student_id', 'academic_year_id', 'school_class_id', 'section_id', 'fee_structure_id', 'status']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertStudentFeeAssignmentRequest $request): JsonResponse
    {
        $assignment = $this->assignments->create(StudentFeeAssignmentData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Student fee assignment created successfully.',
            'data' => new StudentFeeAssignmentResource($assignment),
        ], 201);
    }

    public function show(StudentFeeAssignment $studentFeeAssignment): JsonResponse
    {
        $this->authorize('view', $studentFeeAssignment);

        return response()->json([
            'data' => new StudentFeeAssignmentResource($studentFeeAssignment->load(['student', 'academicYear', 'schoolClass', 'section', 'feeStructure'])),
        ]);
    }

    public function update(UpsertStudentFeeAssignmentRequest $request, StudentFeeAssignment $studentFeeAssignment): JsonResponse
    {
        $assignment = $this->assignments->update($studentFeeAssignment, StudentFeeAssignmentData::fromArray([
            ...$request->validated(),
            'school_id' => $studentFeeAssignment->school_id,
        ]));

        return response()->json([
            'message' => 'Student fee assignment updated successfully.',
            'data' => new StudentFeeAssignmentResource($assignment),
        ]);
    }

    public function destroy(StudentFeeAssignment $studentFeeAssignment): JsonResponse
    {
        $this->authorize('delete', $studentFeeAssignment);
        $this->assignments->delete($studentFeeAssignment);

        return response()->json(null, 204);
    }

    public function assignFeeStructure(Student $student, AssignFeeStructureToStudentRequest $request): JsonResponse
    {
        $feeStructure = FeeStructure::query()->findOrFail((int) $request->validated('fee_structure_id'));
        $assignment = $this->assignments->assignStructureToStudent($student, $feeStructure, $request->validated());

        return response()->json([
            'message' => 'Fee structure assigned to student successfully.',
            'data' => new StudentFeeAssignmentResource($assignment),
        ], 201);
    }
}
