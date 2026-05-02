<?php

namespace App\Repositories\Contracts\Payments;

use App\Models\Payments\PaymentRefund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaymentRefundRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): PaymentRefund;

    public function create(array $attributes): PaymentRefund;

    public function update(PaymentRefund $refund, array $attributes): PaymentRefund;

    public function listByTransaction(int $transactionId): Collection;
}
