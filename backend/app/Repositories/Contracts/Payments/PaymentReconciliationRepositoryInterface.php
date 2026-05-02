<?php

namespace App\Repositories\Contracts\Payments;

use App\Models\Payments\PaymentReconciliation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaymentReconciliationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): PaymentReconciliation;

    public function create(array $attributes): PaymentReconciliation;

    public function listByTransaction(int $transactionId): Collection;
}
