<?php

namespace App\Repositories\Eloquent\Payments;

use App\Models\Payments\UpiPaymentRequest;
use App\Repositories\Contracts\Payments\UpiPaymentRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UpiPaymentRequestRepository implements UpiPaymentRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): UpiPaymentRequest
    {
        return $this->baseQuery()->withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): UpiPaymentRequest
    {
        $request = UpiPaymentRequest::withoutGlobalScopes()->create($attributes);

        return $this->findOrFail($request->id);
    }

    public function update(UpiPaymentRequest $request, array $attributes): UpiPaymentRequest
    {
        $request->update($attributes);

        return $this->findOrFail($request->id);
    }

    public function findByTransactionId(int $transactionId): ?UpiPaymentRequest
    {
        return $this->baseQuery()
            ->where('transaction_id', $transactionId)
            ->first();
    }

    public function expireDue(): int
    {
        return UpiPaymentRequest::withoutGlobalScopes()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['school_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['provider'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('provider', $value));
            })
            ->when($filters['student_id'] ?? null, function (Builder $query, $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('student_id', $value));
            })
            ->when($filters['verification_status'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery->where('verification_status', $value));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }

    protected function baseQuery(): Builder
    {
        return UpiPaymentRequest::withoutGlobalScopes()
            ->with(['transaction.gateway', 'transaction.student']);
    }
}
