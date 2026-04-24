<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\FeeCategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertFeeCategoryRequest;
use App\Http\Resources\Finance\FeeCategoryResource;
use App\Models\Finance\FeeCategory;
use App\Services\Finance\FeeCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeCategoryController extends Controller
{
    public function __construct(
        protected FeeCategoryService $feeCategories,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FeeCategory::class);

        return response()->json([
            'data' => FeeCategoryResource::collection(
                $this->feeCategories->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertFeeCategoryRequest $request): JsonResponse
    {
        $feeCategory = $this->feeCategories->create(FeeCategoryData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Fee category created successfully.',
            'data' => new FeeCategoryResource($feeCategory->loadCount('feeHeads')),
        ], 201);
    }

    public function show(FeeCategory $feeCategory): JsonResponse
    {
        $this->authorize('view', $feeCategory);

        return response()->json([
            'data' => new FeeCategoryResource($feeCategory->loadCount('feeHeads')),
        ]);
    }

    public function update(UpsertFeeCategoryRequest $request, FeeCategory $feeCategory): JsonResponse
    {
        $feeCategory = $this->feeCategories->update($feeCategory, FeeCategoryData::fromArray([
            ...$request->validated(),
            'school_id' => $feeCategory->school_id,
        ]));

        return response()->json([
            'message' => 'Fee category updated successfully.',
            'data' => new FeeCategoryResource($feeCategory),
        ]);
    }

    public function destroy(FeeCategory $feeCategory): JsonResponse
    {
        $this->authorize('delete', $feeCategory);
        $this->feeCategories->delete($feeCategory);

        return response()->json(null, 204);
    }
}
