<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\StoreExamSubjectRequest;
use App\Models\Examination\Exam;
use App\Models\Examination\ExamSubject;
use App\Services\Examination\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamSubjectController extends Controller
{
    public function __construct(
        protected ExamService $exams,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => ['required', 'integer'],
        ]);

        $exam = Exam::query()->findOrFail((int) $request->integer('exam_id'));

        return response()->json([
            'data' => $this->exams->subjectsForExam($exam),
        ]);
    }

    public function store(StoreExamSubjectRequest $request): JsonResponse
    {
        $exam = Exam::query()->findOrFail((int) $request->validated('exam_id'));
        $examSubject = $this->exams->attachSubject($exam, $request->validated());

        return response()->json([
            'message' => 'Exam subject mapped successfully.',
            'data' => $examSubject,
        ], 201);
    }

    public function show(ExamSubject $examSubject): JsonResponse
    {
        return response()->json([
            'data' => $examSubject->load(['exam', 'subject']),
        ]);
    }

    public function destroy(ExamSubject $examSubject): JsonResponse
    {
        $this->exams->detachSubject($examSubject);

        return response()->json(null, 204);
    }
}
