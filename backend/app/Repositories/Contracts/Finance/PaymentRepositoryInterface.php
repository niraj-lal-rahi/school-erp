<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\PaymentData;
use App\Models\Finance\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Payment;

    public function create(PaymentData $data): Payment;

    public function update(Payment $payment, array $attributes): Payment;

    public function delete(Payment $payment): void;

    public function nextPaymentNumber(int $schoolId): string;
}
