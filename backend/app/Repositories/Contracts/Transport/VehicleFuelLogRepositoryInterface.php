<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\VehicleFuelLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VehicleFuelLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): VehicleFuelLog;

    public function create(array $attributes): VehicleFuelLog;

    public function update(VehicleFuelLog $fuelLog, array $attributes): VehicleFuelLog;

    public function delete(VehicleFuelLog $fuelLog): void;
}
