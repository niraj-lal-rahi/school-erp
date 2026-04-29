<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\StoreRevaluationRequest;
use App\Models\Examination\RevaluationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevaluationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = RevaluationRequest::query()
            ->with(['exam', 'student', 'subject'])
            ->when($request->filled('exam_id'), fn ($query) => $query->where('exam_id', $request->integer('exam_id')))
            ->when($request->filled('student_id'), fn ($query) => $query->where('student_id', $request->integer('student_id')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->integer('subject_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json([
            'data' => $requests,
        ]);
    }

    public function store(StoreRevaluationRequest $request): JsonResponse
    {
        $revaluation = RevaluationRequest::query()->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Revaluation request created successfully.',
            'data' => $revaluation->load(['exam', 'student', 'subject']),
        ], 201);
    }

    public function show(RevaluationRequest $revaluation): JsonResponse
    {
        return response()->json([
            'data' => $revaluation->load(['exam', 'student', 'subject']),
        ]);
    }

    public function destroy(RevaluationRequest $revaluation): JsonResponse
    {
        $revaluation->delete();

        return response()->json(null, 204);
    }
}
