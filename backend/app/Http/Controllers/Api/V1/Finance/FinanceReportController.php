<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    public function __construct(
        protected FinanceReportService $reports,
    ) {
    }

    public function feeCollection(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Finance\FeeInvoice::class);

        return response()->json([
            'data' => $this->reports->feeCollection($request->only(['academic_year_id', 'student_id', 'class_id', 'section_id', 'date_from', 'date_to'])),
        ]);
    }

    public function outstandingFees(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Finance\FeeInvoice::class);

        return response()->json([
            'data' => $this->reports->outstandingFees($request->only(['academic_year_id', 'student_id', 'class_id', 'section_id'])),
        ]);
    }

    public function studentLedger(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Finance\FeeInvoice::class);

        return response()->json([
            'data' => $this->reports->studentLedger($request->only(['academic_year_id', 'student_id', 'class_id', 'section_id'])),
        ]);
    }

    public function dailyCollection(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Finance\Payment::class);

        return response()->json([
            'data' => $this->reports->dailyCollection($request->only(['academic_year_id', 'student_id', 'class_id', 'section_id', 'date_from', 'date_to'])),
        ]);
    }

    public function expenseSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Finance\Expense::class);

        return response()->json([
            'data' => $this->reports->expenseSummary($request->only(['expense_category_id', 'status', 'date_from', 'date_to'])),
        ]);
    }

    public function incomeVsExpense(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Finance\Expense::class);

        return response()->json([
            'data' => $this->reports->incomeVsExpense($request->only(['academic_year_id', 'student_id', 'class_id', 'section_id', 'expense_category_id', 'date_from', 'date_to'])),
        ]);
    }
}
