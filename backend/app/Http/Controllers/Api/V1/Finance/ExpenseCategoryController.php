<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\ExpenseCategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertExpenseCategoryRequest;
use App\Http\Resources\Finance\ExpenseCategoryResource;
use App\Models\Finance\ExpenseCategory;
use App\Services\Finance\ExpenseCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        protected ExpenseCategoryService $expenseCategories,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ExpenseCategory::class);

        return response()->json([
            'data' => ExpenseCategoryResource::collection(
                $this->expenseCategories->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertExpenseCategoryRequest $request): JsonResponse
    {
        $expenseCategory = $this->expenseCategories->create(ExpenseCategoryData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Expense category created successfully.',
            'data' => new ExpenseCategoryResource($expenseCategory->loadCount('expenses')),
        ], 201);
    }

    public function show(ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->authorize('view', $expenseCategory);

        return response()->json([
            'data' => new ExpenseCategoryResource($expenseCategory->loadCount('expenses')),
        ]);
    }

    public function update(UpsertExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory = $this->expenseCategories->update($expenseCategory, ExpenseCategoryData::fromArray([
            ...$request->validated(),
            'school_id' => $expenseCategory->school_id,
        ]));

        return response()->json([
            'message' => 'Expense category updated successfully.',
            'data' => new ExpenseCategoryResource($expenseCategory),
        ]);
    }

    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->authorize('delete', $expenseCategory);
        $this->expenseCategories->delete($expenseCategory);

        return response()->json(null, 204);
    }
}
