<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\PaymentData;
use App\Models\Finance\Payment;
use App\Repositories\Contracts\Finance\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $paymentQuery) use ($search): void {
                    $paymentQuery->where('payment_no', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['fee_invoice_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('fee_invoice_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['payment_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('payment_date', '>=', $value))
            ->when($filters['payment_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('payment_date', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Payment
    {
        return $this->query()->findOrFail($id);
    }

    public function create(PaymentData $data): Payment
    {
        $payment = Payment::create($data->attributes);

        if ($data->allocations !== []) {
            $payment->allocations()->createMany($data->allocations);
        }

        return $this->findOrFail($payment->id);
    }

    public function update(Payment $payment, array $attributes): Payment
    {
        $payment->update($attributes);

        return $this->findOrFail($payment->id);
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }

    public function nextPaymentNumber(int $schoolId): string
    {
        $nextId = (int) Payment::withoutGlobalScopes()->where('school_id', $schoolId)->max('id') + 1;

        return 'PAY-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    protected function query(): Builder
    {
        return Payment::query()->with([
            'student',
            'invoice',
            'receiver',
            'receipt',
            'allocations.invoice',
            'allocations.installment',
        ]);
    }
}
