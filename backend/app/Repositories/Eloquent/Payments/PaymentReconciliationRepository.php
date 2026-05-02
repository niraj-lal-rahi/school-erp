<?php

namespace App\Repositories\Eloquent\Payments;

use App\Models\Payments\PaymentReconciliation;
use App\Repositories\Contracts\Payments\PaymentReconciliationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentReconciliationRepository implements PaymentReconciliationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): PaymentReconciliation
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $attributes): PaymentReconciliation
    {
        $reconciliation = PaymentReconciliation::withoutGlobalScopes()->create($attributes);

        return $this->findOrFail($reconciliation->id);
    }

    public function listByTransaction(int $transactionId): Collection
    {
        return $this->baseQuery()
            ->where('transaction_id', $transactionId)
            ->latest('id')
            ->get();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['school_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['provider'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('provider', $value));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('new_status', $value))
            ->when($filters['payment_method'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('payment_method', $value));
            })
            ->when($filters['student_id'] ?? null, function (Builder $query, $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('student_id', $value));
            })
            ->when($filters['payable_type'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('payable_type', $value));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }

    protected function baseQuery(): Builder
    {
        return PaymentReconciliation::withoutGlobalScopes()
            ->with(['transaction', 'reconciler']);
    }
}
