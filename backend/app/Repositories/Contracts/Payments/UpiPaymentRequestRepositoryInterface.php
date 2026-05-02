<?php

namespace App\Repositories\Contracts\Payments;

use App\Models\Payments\UpiPaymentRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UpiPaymentRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): UpiPaymentRequest;

    public function create(array $attributes): UpiPaymentRequest;

    public function update(UpiPaymentRequest $request, array $attributes): UpiPaymentRequest;

    public function findByTransactionId(int $transactionId): ?UpiPaymentRequest;

    public function expireDue(): int;
}
