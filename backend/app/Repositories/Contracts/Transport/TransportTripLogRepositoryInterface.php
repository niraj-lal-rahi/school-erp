<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportTripLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportTripLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportTripLog;

    public function create(array $attributes): TransportTripLog;

    public function update(TransportTripLog $tripLog, array $attributes): TransportTripLog;

    public function delete(TransportTripLog $tripLog): void;
}
