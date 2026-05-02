<?php

namespace App\Repositories\Eloquent\Payments;

use App\Models\Payments\PaymentGateway;
use App\Repositories\Contracts\Payments\PaymentGatewayRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentGatewayRepository implements PaymentGatewayRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): PaymentGateway
    {
        return $this->baseQuery()->withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): PaymentGateway
    {
        $gateway = PaymentGateway::withoutGlobalScopes()->create($attributes);

        return $this->findOrFail($gateway->id);
    }

    public function update(PaymentGateway $gateway, array $attributes): PaymentGateway
    {
        $gateway->update($attributes);

        return $this->findOrFail($gateway->id);
    }

    public function delete(PaymentGateway $gateway): void
    {
        $gateway->delete();
    }

    public function listActiveForTenant(?int $schoolId): Collection
    {
        return $this->baseQuery()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($schoolId): void {
                $query->whereNull('school_id');

                if ($schoolId !== null) {
                    $query->orWhere('school_id', $schoolId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    public function findActiveByProvider(string $provider, ?int $schoolId = null): ?PaymentGateway
    {
        return $this->baseQuery()
            ->where('provider', $provider)
            ->where('status', 'active')
            ->where(function (Builder $query) use ($schoolId): void {
                $query->whereNull('school_id');

                if ($schoolId !== null) {
                    $query->orWhere('school_id', $schoolId);
                }
            })
            ->orderByRaw('CASE WHEN school_id IS NULL THEN 1 ELSE 0 END')
            ->first();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['provider'] ?? null, fn (Builder $query, string $value) => $query->where('provider', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['school_id'] ?? null, function (Builder $query, $value): void {
                $query->where(function (Builder $scopeQuery) use ($value): void {
                    $scopeQuery->whereNull('school_id')
                        ->orWhere('school_id', $value);
                });
            });
    }

    protected function baseQuery(): Builder
    {
        return PaymentGateway::withoutGlobalScopes()
            ->with('credentials');
    }
}
