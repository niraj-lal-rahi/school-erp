<?php

namespace App\Services\Finance;

use App\Models\Finance\Expense;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FinanceReportService
{
    public function feeCollection(array $filters = []): array
    {
        $query = $this->paymentQuery($filters)->where('status', 'successful');

        return [
            'summary' => [
                'total_collected' => round((float) $query->sum('amount'), 2),
                'payments_count' => (clone $query)->count(),
            ],
            'by_method' => $this->paymentQuery($filters)
                ->where('status', 'successful')
                ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as payments_count')
                ->groupBy('payment_method')
                ->orderBy('payment_method')
                ->get()
                ->map(fn ($row) => [
                    'payment_method' => $row->payment_method,
                    'total_amount' => round((float) $row->total_amount, 2),
                    'payments_count' => (int) $row->payments_count,
                ])
                ->values()
                ->all(),
        ];
    }

    public function outstandingFees(array $filters = []): array
    {
        $query = $this->invoiceQuery($filters)
            ->where('balance_amount', '>', 0)
            ->whereNotIn('status', ['cancelled']);

        return [
            'summary' => [
                'outstanding_total' => round((float) $query->sum('balance_amount'), 2),
                'students_with_dues' => (clone $query)->distinct('student_id')->count('student_id'),
                'invoices_count' => (clone $query)->count(),
            ],
            'top_outstanding_students' => $this->invoiceQuery($filters)
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', ['cancelled'])
                ->selectRaw('student_id, SUM(balance_amount) as outstanding_total')
                ->with('student:id,full_name,admission_no')
                ->groupBy('student_id')
                ->orderByDesc('outstanding_total')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'student_id' => $row->student_id,
                    'student' => $row->student ? [
                        'id' => $row->student->id,
                        'full_name' => $row->student->full_name,
                        'admission_no' => $row->student->admission_no,
                    ] : null,
                    'outstanding_total' => round((float) $row->outstanding_total, 2),
                ])
                ->values()
                ->all(),
        ];
    }

    public function studentLedger(array $filters = []): array
    {
        $studentId = $filters['student_id'] ?? null;

        $invoiceRows = $this->invoiceQuery($filters)
            ->when($studentId, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->orderBy('issue_date')
            ->get()
            ->map(fn (FeeInvoice $invoice) => [
                'type' => 'invoice',
                'reference_no' => $invoice->invoice_no,
                'date' => optional($invoice->issue_date)->toDateString(),
                'debit' => round((float) $invoice->grand_total, 2),
                'credit' => 0.0,
                'balance_effect' => round((float) $invoice->grand_total, 2),
                'status' => $invoice->status,
                'student' => $invoice->student ? [
                    'id' => $invoice->student->id,
                    'full_name' => $invoice->student->full_name,
                    'admission_no' => $invoice->student->admission_no,
                ] : null,
            ]);

        $paymentRows = $this->paymentQuery($filters)
            ->when($studentId, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->whereIn('status', ['successful', 'refunded'])
            ->orderBy('payment_date')
            ->get()
            ->map(fn (Payment $payment) => [
                'type' => 'payment',
                'reference_no' => $payment->payment_no,
                'date' => optional($payment->payment_date)->toDateString(),
                'debit' => 0.0,
                'credit' => round((float) $payment->amount, 2),
                'balance_effect' => round((float) $payment->amount * -1, 2),
                'status' => $payment->status,
                'student' => $payment->student ? [
                    'id' => $payment->student->id,
                    'full_name' => $payment->student->full_name,
                    'admission_no' => $payment->student->admission_no,
                ] : null,
            ]);

        $entries = $invoiceRows
            ->concat($paymentRows)
            ->sortBy('date')
            ->values();

        $running = 0.0;
        $entries = $entries->map(function (array $entry) use (&$running) {
            $running += $entry['balance_effect'];
            $entry['running_balance'] = round($running, 2);

            return $entry;
        });

        return [
            'summary' => [
                'total_invoiced' => round((float) $invoiceRows->sum('debit'), 2),
                'total_paid' => round((float) $paymentRows->sum('credit'), 2),
                'closing_balance' => round($running, 2),
            ],
            'entries' => $entries->all(),
        ];
    }

    public function dailyCollection(array $filters = []): array
    {
        $rows = $this->paymentQuery($filters)
            ->where('status', 'successful')
            ->selectRaw('payment_date, SUM(amount) as total_amount, COUNT(*) as payments_count')
            ->groupBy('payment_date')
            ->orderBy('payment_date')
            ->get()
            ->map(fn ($row) => [
                'payment_date' => optional($row->payment_date)->toDateString(),
                'total_amount' => round((float) $row->total_amount, 2),
                'payments_count' => (int) $row->payments_count,
            ])
            ->values();

        return [
            'summary' => [
                'days_count' => $rows->count(),
                'average_daily_collection' => round($rows->avg('total_amount') ?? 0, 2),
            ],
            'rows' => $rows->all(),
        ];
    }

    public function expenseSummary(array $filters = []): array
    {
        $query = $this->expenseQuery($filters)->whereIn('status', ['approved', 'paid']);

        return [
            'summary' => [
                'total_expenses' => round((float) $query->sum('amount'), 2),
                'expenses_count' => (clone $query)->count(),
            ],
            'by_category' => $this->expenseQuery($filters)
                ->whereIn('status', ['approved', 'paid'])
                ->selectRaw('expense_category_id, SUM(amount) as total_amount, COUNT(*) as expenses_count')
                ->with('category:id,name,code')
                ->groupBy('expense_category_id')
                ->orderByDesc('total_amount')
                ->get()
                ->map(fn ($row) => [
                    'expense_category_id' => $row->expense_category_id,
                    'category' => $row->category ? [
                        'id' => $row->category->id,
                        'name' => $row->category->name,
                        'code' => $row->category->code,
                    ] : null,
                    'total_amount' => round((float) $row->total_amount, 2),
                    'expenses_count' => (int) $row->expenses_count,
                ])
                ->values()
                ->all(),
        ];
    }

    public function incomeVsExpense(array $filters = []): array
    {
        $income = round((float) $this->paymentQuery($filters)->where('status', 'successful')->sum('amount'), 2);
        $expense = round((float) $this->expenseQuery($filters)->whereIn('status', ['approved', 'paid'])->sum('amount'), 2);

        return [
            'summary' => [
                'income_total' => $income,
                'expense_total' => $expense,
                'net_surplus' => round($income - $expense, 2),
            ],
            'breakdown' => [
                ['label' => 'Income', 'amount' => $income],
                ['label' => 'Expense', 'amount' => $expense],
            ],
        ];
    }

    protected function invoiceQuery(array $filters = []): Builder
    {
        return FeeInvoice::query()
            ->with('student:id,full_name,admission_no')
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when(($filters['class_id'] ?? null) || ($filters['section_id'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($filters): void {
                    $studentQuery->whereHas('enrollments', function (Builder $enrollmentQuery) use ($filters): void {
                        $enrollmentQuery->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
                            ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('school_class_id', $value))
                            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('section_id', $value));
                    });
                });
            });
    }

    protected function paymentQuery(array $filters = []): Builder
    {
        return Payment::query()
            ->with('student:id,full_name,admission_no')
            ->when($filters['academic_year_id'] ?? null, function (Builder $query, int|string $value): void {
                $query->whereHas('invoice', fn (Builder $invoiceQuery) => $invoiceQuery->where('academic_year_id', $value));
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('payment_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('payment_date', '<=', $value))
            ->when(($filters['class_id'] ?? null) || ($filters['section_id'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($filters): void {
                    $studentQuery->whereHas('enrollments', function (Builder $enrollmentQuery) use ($filters): void {
                        $enrollmentQuery->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
                            ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('school_class_id', $value))
                            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('section_id', $value));
                    });
                });
            });
    }

    protected function expenseQuery(array $filters = []): Builder
    {
        return Expense::query()
            ->with('category:id,name,code')
            ->when($filters['expense_category_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('expense_category_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('expense_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('expense_date', '<=', $value));
    }
}
