<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\PublishResultRequest;
use App\Models\Examination\Exam;
use App\Models\Examination\ResultPublication;
use App\Services\Examination\ResultPublicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultPublicationController extends Controller
{
    public function __construct(
        protected ResultPublicationService $publications,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $publications = ResultPublication::query()
            ->with(['exam', 'publisher'])
            ->when($request->filled('exam_id'), fn ($query) => $query->where('exam_id', $request->integer('exam_id')))
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json([
            'data' => $publications,
        ]);
    }

    public function show(ResultPublication $resultPublication): JsonResponse
    {
        return response()->json([
            'data' => $resultPublication->load(['exam', 'publisher']),
        ]);
    }

    public function publish(PublishResultRequest $request, int $examId): JsonResponse
    {
        $exam = Exam::query()->findOrFail($examId);
        $publication = $this->publications->publish($exam, $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Results published successfully.',
            'data' => $publication,
        ]);
    }
}
