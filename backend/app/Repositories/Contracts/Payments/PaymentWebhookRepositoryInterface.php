<?php

namespace App\Repositories\Contracts\Payments;

use App\Models\Payments\PaymentWebhookEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentWebhookRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): PaymentWebhookEvent;

    public function create(array $attributes): PaymentWebhookEvent;

    public function update(PaymentWebhookEvent $event, array $attributes): PaymentWebhookEvent;

    public function findByProviderEventId(string $provider, ?string $eventId): ?PaymentWebhookEvent;

    public function findDuplicate(string $provider, ?string $eventId, string $payload, ?string $signature = null): ?PaymentWebhookEvent;
}
