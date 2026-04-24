<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\ExpenseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertExpenseRequest;
use App\Http\Resources\Finance\ExpenseResource;
use App\Models\Finance\Expense;
use App\Services\Finance\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenses,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        return response()->json(
            ExpenseResource::collection($this->expenses->paginate(
                $request->only(['search', 'expense_category_id', 'status', 'expense_date_from', 'expense_date_to']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenses->create(ExpenseData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Expense created successfully.',
            'data' => new ExpenseResource($expense),
        ], 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        return response()->json([
            'data' => new ExpenseResource($expense->load(['category', 'creator', 'approver'])),
        ]);
    }

    public function update(UpsertExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense = $this->expenses->update($expense, ExpenseData::fromArray([
            ...$request->validated(),
            'school_id' => $expense->school_id,
            'expense_no' => $expense->expense_no,
            'created_by' => $expense->created_by,
            'approved_by' => $expense->approved_by,
            'approved_at' => $expense->approved_at,
            'paid_at' => $expense->paid_at,
            'status' => $request->validated('status') ?? $expense->status,
        ]));

        return response()->json([
            'message' => 'Expense updated successfully.',
            'data' => new ExpenseResource($expense),
        ]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);
        $this->expenses->delete($expense);

        return response()->json(null, 204);
    }

    public function approve(Expense $expense, Request $request): JsonResponse
    {
        $this->authorize('update', $expense);
        $expense = $this->expenses->approve($expense, $request->user()->id);

        return response()->json([
            'message' => 'Expense approved successfully.',
            'data' => new ExpenseResource($expense),
        ]);
    }

    public function markPaid(Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);
        $expense = $this->expenses->markPaid($expense);

        return response()->json([
            'message' => 'Expense marked as paid successfully.',
            'data' => new ExpenseResource($expense),
        ]);
    }
}
