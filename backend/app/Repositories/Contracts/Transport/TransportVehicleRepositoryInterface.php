<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportVehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportVehicleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportVehicle;

    public function create(array $attributes): TransportVehicle;

    public function update(TransportVehicle $vehicle, array $attributes): TransportVehicle;

    public function delete(TransportVehicle $vehicle): void;
}
