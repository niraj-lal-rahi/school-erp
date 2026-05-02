<?php

namespace App\Repositories\Contracts\Payments;

use App\Models\Payments\PaymentTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaymentTransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): PaymentTransaction;

    public function create(array $attributes): PaymentTransaction;

    public function update(PaymentTransaction $transaction, array $attributes): PaymentTransaction;

    public function findByGatewayPaymentId(string $gatewayPaymentId, ?int $schoolId = null): ?PaymentTransaction;

    public function findByGatewayOrderId(string $gatewayOrderId, ?int $schoolId = null): ?PaymentTransaction;

    public function findByTransactionNo(string $transactionNo, int $schoolId): ?PaymentTransaction;

    public function listByPayable(string $payableType, ?int $payableId, int $schoolId): Collection;
}
