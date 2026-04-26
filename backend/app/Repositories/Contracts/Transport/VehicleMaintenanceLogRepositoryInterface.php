<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\VehicleMaintenanceLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VehicleMaintenanceLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): VehicleMaintenanceLog;

    public function create(array $attributes): VehicleMaintenanceLog;

    public function update(VehicleMaintenanceLog $maintenanceLog, array $attributes): VehicleMaintenanceLog;

    public function delete(VehicleMaintenanceLog $maintenanceLog): void;
}
