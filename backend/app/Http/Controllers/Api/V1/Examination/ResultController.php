<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Models\Examination\Exam;
use App\Models\Examination\StudentResult;
use App\Repositories\Eloquent\Examination\StudentResultRepository;
use App\Services\Examination\MeritListService;
use App\Services\Examination\ResultComputationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        protected StudentResultRepository $results,
        protected ResultComputationService $computation,
        protected MeritListService $meritLists,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->results->paginate(
                $request->only([
                    'search',
                    'academic_year_id',
                    'class_id',
                    'section_id',
                    'exam_type_id',
                    'exam_id',
                    'student_id',
                    'result_status',
                ]),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function show(StudentResult $studentResult): JsonResponse
    {
        return response()->json([
            'data' => $this->results->findOrFail($studentResult->id),
        ]);
    }

    public function compute(Request $request, int $examId): JsonResponse
    {
        $request->validate([
            'grading_system_id' => ['nullable', 'integer'],
        ]);

        $exam = Exam::query()->findOrFail($examId);
        $results = $this->computation->computeForExam($exam, $request->integer('grading_system_id') ?: null);

        return response()->json([
            'message' => 'Results computed successfully.',
            'data' => $results,
        ]);
    }

    public function studentResult(int $examId, int $studentId): JsonResponse
    {
        $result = $this->results->firstForExamStudent($examId, $studentId);

        abort_unless($result, 404);

        return response()->json([
            'data' => $result,
        ]);
    }

    public function classResults(Request $request, int $examId, int $classId): JsonResponse
    {
        $sectionId = $request->integer('section_id') ?: null;
        $results = $this->results->paginate([
            'exam_id' => $examId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'result_status' => $request->input('result_status'),
            'search' => $request->input('search'),
        ], (int) $request->integer('per_page', 50));

        return response()->json([
            'data' => $results,
        ]);
    }

    public function meritList(Request $request, int $examId): JsonResponse
    {
        $exam = Exam::query()->findOrFail($examId);
        $results = $this->meritLists->assignRanks($exam);

        $sectionId = $request->integer('section_id') ?: null;
        if ($sectionId) {
            $results = $results->filter(fn ($result) => (int) $result->exam?->section_id === $sectionId)->values();
        }

        return response()->json([
            'data' => $results,
        ]);
    }
}
