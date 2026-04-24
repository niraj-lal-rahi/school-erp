<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\ReceiptData;
use App\Models\Finance\Payment;
use App\Models\Finance\Receipt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReceiptRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Receipt;

    public function create(ReceiptData $data): Receipt;

    public function forPayment(Payment $payment): ?Receipt;

    public function nextReceiptNumber(int $schoolId): string;
}
