<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\StoreExamRequest;
use App\Http\Requests\Examination\UpdateExamRequest;
use App\Models\Examination\Exam;
use App\Services\Examination\ExamEnrollmentService;
use App\Services\Examination\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        protected ExamService $exams,
        protected ExamEnrollmentService $enrollments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->exams->paginate(
                $request->only([
                    'search',
                    'academic_year_id',
                    'class_id',
                    'section_id',
                    'exam_type_id',
                    'exam_id',
                    'result_status',
                    'date_from',
                    'date_to',
                ]),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        $exam = $this->exams->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Exam created successfully.',
            'data' => $exam,
        ], 201);
    }

    public function show(Exam $exam): JsonResponse
    {
        return response()->json([
            'data' => $this->exams->findOrFail($exam->id)->load(['examSubjects.subject', 'studentExamEnrollments.student']),
        ]);
    }

    public function update(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        $exam = $this->exams->update($exam, $request->validated());

        return response()->json([
            'message' => 'Exam updated successfully.',
            'data' => $exam,
        ]);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->exams->delete($exam);

        return response()->json(null, 204);
    }

    public function enrollStudents(Exam $exam): JsonResponse
    {
        $enrollments = $this->enrollments->enrollForExam($exam);

        return response()->json([
            'message' => 'Students enrolled successfully.',
            'data' => $enrollments,
        ]);
    }
}
