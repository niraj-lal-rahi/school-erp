<?php

namespace App\Repositories\Eloquent\Payments;

use App\Models\Payments\PaymentWebhookEvent;
use App\Repositories\Contracts\Payments\PaymentWebhookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PaymentWebhookRepository implements PaymentWebhookRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): PaymentWebhookEvent
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $attributes): PaymentWebhookEvent
    {
        $event = PaymentWebhookEvent::withoutGlobalScopes()->create($attributes);

        return $this->findOrFail($event->id);
    }

    public function update(PaymentWebhookEvent $event, array $attributes): PaymentWebhookEvent
    {
        $event->update($attributes);

        return $this->findOrFail($event->id);
    }

    public function findByProviderEventId(string $provider, ?string $eventId): ?PaymentWebhookEvent
    {
        if ($eventId === null || $eventId === '') {
            return null;
        }

        return $this->baseQuery()
            ->where('provider', $provider)
            ->where('event_id', $eventId)
            ->first();
    }

    public function findDuplicate(string $provider, ?string $eventId, string $payload, ?string $signature = null): ?PaymentWebhookEvent
    {
        return $this->baseQuery()
            ->where('provider', $provider)
            ->where(function (Builder $query) use ($eventId, $payload, $signature): void {
                if ($eventId !== null && $eventId !== '') {
                    $query->where('event_id', $eventId);

                    return;
                }

                $query->where('payload', $payload);

                if ($signature !== null && $signature !== '') {
                    $query->where('signature', $signature);
                }
            })
            ->first();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['school_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['provider'] ?? null, fn (Builder $query, string $value) => $query->where('provider', $value))
            ->when(isset($filters['processed']), fn (Builder $query) => $query->where('processed', (bool) $filters['processed']))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }

    protected function baseQuery(): Builder
    {
        return PaymentWebhookEvent::withoutGlobalScopes()->newQuery();
    }
}
