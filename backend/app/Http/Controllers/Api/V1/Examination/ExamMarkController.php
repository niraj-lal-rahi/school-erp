<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\BulkStoreExamMarkRequest;
use App\Http\Requests\Examination\StoreExamMarkRequest;
use App\Models\Examination\ExamMark;
use App\Services\Examination\MarksEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamMarkController extends Controller
{
    public function __construct(
        protected MarksEntryService $marks,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->marks->paginate(
                $request->only([
                    'search',
                    'academic_year_id',
                    'class_id',
                    'section_id',
                    'exam_id',
                    'student_id',
                    'subject_id',
                    'is_absent',
                ]),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreExamMarkRequest $request): JsonResponse
    {
        $mark = $this->marks->store($request->validated(), $request->user()->id);

        return response()->json([
            'message' => 'Marks saved successfully.',
            'data' => $mark,
        ], 201);
    }

    public function show(ExamMark $examMark): JsonResponse
    {
        return response()->json([
            'data' => $this->marks->findOrFail($examMark->id),
        ]);
    }

    public function update(StoreExamMarkRequest $request, ExamMark $examMark): JsonResponse
    {
        $mark = $this->marks->store([
            ...$request->validated(),
            'exam_id' => $examMark->exam_id,
            'student_id' => $examMark->student_id,
            'subject_id' => $examMark->subject_id,
        ], $request->user()->id);

        return response()->json([
            'message' => 'Marks updated successfully.',
            'data' => $mark,
        ]);
    }

    public function destroy(ExamMark $examMark): JsonResponse
    {
        $examMark->delete();

        return response()->json(null, 204);
    }

    public function bulkStore(BulkStoreExamMarkRequest $request): JsonResponse
    {
        $examId = (int) $request->validated('exam_id');
        $records = collect($request->validated('records'))
            ->map(fn (array $record): array => [
                ...$record,
                'exam_id' => $examId,
            ])
            ->all();

        $marks = $this->marks->bulkStore($records, $request->user()->id);

        return response()->json([
            'message' => 'Bulk marks saved successfully.',
            'data' => $marks,
        ]);
    }
}
