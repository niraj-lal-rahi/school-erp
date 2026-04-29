<?php

namespace App\Services\Reports;

use App\Models\Finance\Payment;
use App\Services\Finance\FinanceReportService as SourceFinanceReportService;
use Illuminate\Database\Eloquent\Builder;

class FinanceReportService
{
    public function __construct(
        protected SourceFinanceReportService $source,
    ) {
    }

    public function dailyCollection(array $filters = []): array
    {
        return $this->source->dailyCollection($filters);
    }

    public function monthlyCollection(array $filters = []): array
    {
        $rows = $this->paymentQuery($filters)
            ->where('status', 'successful')
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as collection_month, SUM(amount) as total_amount, COUNT(*) as payments_count")
            ->groupBy('collection_month')
            ->orderBy('collection_month')
            ->get()
            ->map(fn ($row) => [
                'collection_month' => $row->collection_month,
                'total_amount' => round((float) $row->total_amount, 2),
                'payments_count' => (int) $row->payments_count,
            ])
            ->values();

        return [
            'summary' => [
                'months_count' => $rows->count(),
                'average_monthly_collection' => round((float) ($rows->avg('total_amount') ?? 0), 2),
            ],
            'rows' => $rows->all(),
        ];
    }

    public function outstandingDues(array $filters = []): array
    {
        return $this->source->outstandingFees($filters);
    }

    public function paymentMethodSplit(array $filters = []): array
    {
        $payload = $this->source->feeCollection($filters);

        return [
            'summary' => $payload['summary'] ?? [],
            'rows' => $payload['by_method'] ?? [],
        ];
    }

    public function overview(array $filters = []): array
    {
        return [
            'fee_collection' => $this->source->feeCollection($filters),
            'outstanding' => $this->source->outstandingFees($filters),
            'daily_collection' => $this->source->dailyCollection($filters),
            'monthly_collection' => $this->monthlyCollection($filters),
        ];
    }

    protected function paymentQuery(array $filters = []): Builder
    {
        return Payment::query()
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('payment_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('payment_date', '<=', $value))
            ->when($filters['payment_method'] ?? null, fn (Builder $query, string $value) => $query->where('payment_method', $value));
    }
}
