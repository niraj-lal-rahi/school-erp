<?php

namespace App\Services\Transport;

use App\Models\Transport\TransportDriver;
use App\Repositories\Contracts\Transport\TransportDriverRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransportDriverService
{
    public function __construct(
        protected TransportDriverRepositoryInterface $drivers,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->drivers->paginate($filters, $perPage);
    }

    public function show(TransportDriver $driver): TransportDriver
    {
        return $this->drivers->findOrFail($driver->id);
    }

    public function create(array $attributes): TransportDriver
    {
        $attributes['full_name'] = $this->resolveFullName($attributes);

        return DB::transaction(fn (): TransportDriver => $this->drivers->create($attributes));
    }

    public function update(TransportDriver $driver, array $attributes): TransportDriver
    {
        $attributes['full_name'] = $this->resolveFullName($attributes, $driver);

        return DB::transaction(fn (): TransportDriver => $this->drivers->update($driver, $attributes));
    }

    public function delete(TransportDriver $driver): void
    {
        DB::transaction(function () use ($driver): void {
            $this->drivers->delete($driver);
        });
    }

    protected function resolveFullName(array $attributes, ?TransportDriver $driver = null): string
    {
        if (! empty($attributes['full_name'])) {
            return trim((string) $attributes['full_name']);
        }

        return trim(implode(' ', array_filter([
            $attributes['first_name'] ?? $driver?->first_name,
            $attributes['middle_name'] ?? $driver?->middle_name,
            $attributes['last_name'] ?? $driver?->last_name,
        ])));
    }
}
