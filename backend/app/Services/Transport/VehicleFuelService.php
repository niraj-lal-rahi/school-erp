<?php

namespace App\Services\Transport;

use App\Models\Transport\VehicleFuelLog;
use App\Models\Transport\TransportVehicle;
use App\Repositories\Contracts\Transport\VehicleFuelLogRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportVehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VehicleFuelService
{
    public function __construct(
        protected VehicleFuelLogRepositoryInterface $fuelLogs,
        protected TransportVehicleRepositoryInterface $vehicles,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->fuelLogs->paginate($filters, $perPage);
    }

    public function show(VehicleFuelLog $fuelLog): VehicleFuelLog
    {
        return $this->fuelLogs->findOrFail($fuelLog->id);
    }

    public function create(array $attributes): VehicleFuelLog
    {
        return DB::transaction(function () use ($attributes): VehicleFuelLog {
            $fuelLog = $this->fuelLogs->create($attributes);
            $this->syncVehicleOdometer((int) $attributes['vehicle_id'], $attributes['odometer_reading'] ?? null);

            return $fuelLog;
        });
    }

    public function update(VehicleFuelLog $fuelLog, array $attributes): VehicleFuelLog
    {
        return DB::transaction(function () use ($fuelLog, $attributes): VehicleFuelLog {
            $updated = $this->fuelLogs->update($fuelLog, $attributes);
            $this->syncVehicleOdometer((int) $updated->vehicle_id, $attributes['odometer_reading'] ?? null);

            return $updated;
        });
    }

    public function delete(VehicleFuelLog $fuelLog): void
    {
        DB::transaction(function () use ($fuelLog): void {
            $this->fuelLogs->delete($fuelLog);
        });
    }

    protected function syncVehicleOdometer(int $vehicleId, mixed $odometerReading): void
    {
        if ($odometerReading === null || $odometerReading === '') {
            return;
        }

        /** @var TransportVehicle $vehicle */
        $vehicle = $this->vehicles->findOrFail($vehicleId);
        $reading = (int) $odometerReading;

        if ($vehicle->odometer_reading === null || $reading >= (int) $vehicle->odometer_reading) {
            $this->vehicles->update($vehicle, ['odometer_reading' => $reading]);
        }
    }
}
