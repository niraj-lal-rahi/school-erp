<?php

namespace App\Services\Transport;

use App\Models\Transport\VehicleMaintenanceLog;
use App\Repositories\Contracts\Transport\VehicleMaintenanceLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VehicleMaintenanceService
{
    public function __construct(
        protected VehicleMaintenanceLogRepositoryInterface $maintenanceLogs,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->maintenanceLogs->paginate($filters, $perPage);
    }

    public function show(VehicleMaintenanceLog $maintenanceLog): VehicleMaintenanceLog
    {
        return $this->maintenanceLogs->findOrFail($maintenanceLog->id);
    }

    public function create(array $attributes): VehicleMaintenanceLog
    {
        return DB::transaction(fn (): VehicleMaintenanceLog => $this->maintenanceLogs->create($attributes));
    }

    public function update(VehicleMaintenanceLog $maintenanceLog, array $attributes): VehicleMaintenanceLog
    {
        return DB::transaction(
            fn (): VehicleMaintenanceLog => $this->maintenanceLogs->update($maintenanceLog, $attributes)
        );
    }

    public function delete(VehicleMaintenanceLog $maintenanceLog): void
    {
        DB::transaction(function () use ($maintenanceLog): void {
            $this->maintenanceLogs->delete($maintenanceLog);
        });
    }
}
