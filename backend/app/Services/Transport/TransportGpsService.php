<?php

namespace App\Services\Transport;

use App\Models\Transport\TransportGpsLog;
use App\Models\Transport\TransportTrip;
use App\Repositories\Contracts\Transport\TransportGpsLogRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportTripRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportVehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportGpsService
{
    public function __construct(
        protected TransportGpsLogRepositoryInterface $gpsLogs,
        protected TransportTripRepositoryInterface $trips,
        protected TransportVehicleRepositoryInterface $vehicles,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->gpsLogs->paginate($filters, $perPage);
    }

    public function show(TransportGpsLog $gpsLog): TransportGpsLog
    {
        return $this->gpsLogs->findOrFail($gpsLog->id);
    }

    public function create(array $attributes): TransportGpsLog
    {
        return DB::transaction(function () use ($attributes): TransportGpsLog {
            $this->guardGpsReferences($attributes);

            return $this->gpsLogs->create($attributes);
        });
    }

    public function latestVehicleLocation(int $vehicleId): ?TransportGpsLog
    {
        return $this->gpsLogs->latestVehicleLocation($vehicleId);
    }

    protected function guardGpsReferences(array $attributes): void
    {
        if (! empty($attributes['transport_trip_id'])) {
            /** @var TransportTrip $trip */
            $trip = $this->trips->findOrFail((int) $attributes['transport_trip_id']);

            if (! empty($attributes['vehicle_id']) && (int) $trip->vehicle_id !== (int) $attributes['vehicle_id']) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['Vehicle does not match the selected trip.'],
                ]);
            }

            if (! empty($attributes['driver_id']) && (int) $trip->driver_id !== (int) $attributes['driver_id']) {
                throw ValidationException::withMessages([
                    'driver_id' => ['Driver does not match the selected trip.'],
                ]);
            }
        }

        if (! empty($attributes['vehicle_id'])) {
            $this->vehicles->findOrFail((int) $attributes['vehicle_id']);
        }
    }
}
