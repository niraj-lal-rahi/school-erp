<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\StoreExamTypeRequest;
use App\Http\Requests\Examination\UpdateExamTypeRequest;
use App\Models\Examination\ExamType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $examTypes = ExamType::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json(['data' => $examTypes]);
    }

    public function store(StoreExamTypeRequest $request): JsonResponse
    {
        $examType = ExamType::query()->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Exam type created successfully.',
            'data' => $examType,
        ], 201);
    }

    public function show(ExamType $examType): JsonResponse
    {
        return response()->json([
            'data' => $examType->loadCount('exams'),
        ]);
    }

    public function update(UpdateExamTypeRequest $request, ExamType $examType): JsonResponse
    {
        $examType->update($request->validated());

        return response()->json([
            'message' => 'Exam type updated successfully.',
            'data' => $examType->refresh(),
        ]);
    }

    public function destroy(ExamType $examType): JsonResponse
    {
        $examType->delete();

        return response()->json(null, 204);
    }
}
