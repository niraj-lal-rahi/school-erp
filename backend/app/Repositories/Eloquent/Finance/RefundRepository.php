<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\RefundData;
use App\Models\Finance\Refund;
use App\Repositories\Contracts\Finance\RefundRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RefundRepository implements RefundRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $refundQuery) use ($search): void {
                    $refundQuery->where('refund_no', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        })
                        ->orWhereHas('payment', function (Builder $paymentQuery) use ($search): void {
                            $paymentQuery->where('payment_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['payment_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('payment_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(RefundData $data): Refund
    {
        return Refund::create($data->attributes);
    }

    public function update(Refund $refund, array $attributes): Refund
    {
        $refund->update($attributes);

        return $this->query()->findOrFail($refund->id);
    }

    public function delete(Refund $refund): void
    {
        $refund->delete();
    }

    public function nextRefundNumber(int $schoolId): string
    {
        $nextId = (int) Refund::withoutGlobalScopes()->where('school_id', $schoolId)->max('id') + 1;

        return 'RFD-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    public function totalProcessedAmountForPayment(int $paymentId, ?int $ignoreRefundId = null): float
    {
        return (float) Refund::query()
            ->where('payment_id', $paymentId)
            ->where('status', 'processed')
            ->when($ignoreRefundId, fn (Builder $query, int $value) => $query->whereKeyNot($value))
            ->sum('amount');
    }

    protected function query(): Builder
    {
        return Refund::query()->with(['payment', 'student', 'approver', 'processor']);
    }
}
