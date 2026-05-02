<?php

namespace App\Repositories\Contracts\Payments;

use App\Models\Payments\PaymentGateway;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaymentGatewayRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): PaymentGateway;

    public function create(array $attributes): PaymentGateway;

    public function update(PaymentGateway $gateway, array $attributes): PaymentGateway;

    public function delete(PaymentGateway $gateway): void;

    public function listActiveForTenant(?int $schoolId): Collection;

    public function findActiveByProvider(string $provider, ?int $schoolId = null): ?PaymentGateway;
}
