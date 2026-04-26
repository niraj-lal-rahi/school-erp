<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportDriver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportDriverRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportDriver;

    public function create(array $attributes): TransportDriver;

    public function update(TransportDriver $driver, array $attributes): TransportDriver;

    public function delete(TransportDriver $driver): void;
}
