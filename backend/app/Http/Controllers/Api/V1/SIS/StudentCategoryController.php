<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentCategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpsertStudentCategoryRequest;
use App\Http\Resources\SIS\StudentCategoryResource;
use App\Models\StudentCategory;
use App\Services\SIS\StudentCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCategoryController extends Controller
{
    public function __construct(
        protected StudentCategoryService $categories,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentCategory::class);

        return response()->json([
            'data' => StudentCategoryResource::collection(
                $this->categories->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertStudentCategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create(
            StudentCategoryData::fromArray([
                ...$request->validated(),
                'school_id' => $request->user()->school_id,
            ])
        );

        return response()->json([
            'message' => 'Student category created successfully.',
            'data' => new StudentCategoryResource($category->loadCount('students')),
        ], 201);
    }

    public function update(UpsertStudentCategoryRequest $request, StudentCategory $studentCategory): JsonResponse
    {
        $category = $this->categories->update($studentCategory, StudentCategoryData::fromArray([
            ...$request->validated(),
            'school_id' => $studentCategory->school_id,
            'uuid' => $studentCategory->uuid,
        ]));

        return response()->json([
            'message' => 'Student category updated successfully.',
            'data' => new StudentCategoryResource($category),
        ]);
    }

    public function destroy(StudentCategory $studentCategory): JsonResponse
    {
        $this->authorize('delete', $studentCategory);
        $this->categories->delete($studentCategory);

        return response()->json(null, 204);
    }
}
