<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\ReceiptData;
use App\Models\Finance\Payment;
use App\Models\Finance\Receipt;
use App\Repositories\Contracts\Finance\ReceiptRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReceiptRepository implements ReceiptRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $receiptQuery) use ($search): void {
                    $receiptQuery->where('receipt_no', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['receipt_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('receipt_date', '>=', $value))
            ->when($filters['receipt_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('receipt_date', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Receipt
    {
        return $this->query()->findOrFail($id);
    }

    public function create(ReceiptData $data): Receipt
    {
        $receipt = Receipt::create($data->attributes);

        return $this->findOrFail($receipt->id);
    }

    public function forPayment(Payment $payment): ?Receipt
    {
        return $this->query()->where('payment_id', $payment->id)->first();
    }

    public function nextReceiptNumber(int $schoolId): string
    {
        $nextId = (int) Receipt::withoutGlobalScopes()->where('school_id', $schoolId)->max('id') + 1;

        return 'RCT-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    protected function query(): Builder
    {
        return Receipt::query()->with(['payment', 'student', 'issuer']);
    }
}
