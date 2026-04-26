<?php

namespace App\Services\Transport;

use App\Models\Transport\TransportVehicle;
use App\Repositories\Contracts\Transport\TransportGpsLogRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportVehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransportVehicleService
{
    public function __construct(
        protected TransportVehicleRepositoryInterface $vehicles,
        protected TransportGpsLogRepositoryInterface $gpsLogs,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->vehicles->paginate($filters, $perPage);
    }

    public function show(TransportVehicle $vehicle): TransportVehicle
    {
        return $this->vehicles->findOrFail($vehicle->id);
    }

    public function create(array $attributes): TransportVehicle
    {
        return DB::transaction(fn (): TransportVehicle => $this->vehicles->create($attributes));
    }

    public function update(TransportVehicle $vehicle, array $attributes): TransportVehicle
    {
        return DB::transaction(fn (): TransportVehicle => $this->vehicles->update($vehicle, $attributes));
    }

    public function delete(TransportVehicle $vehicle): void
    {
        DB::transaction(function () use ($vehicle): void {
            $this->vehicles->delete($vehicle);
        });
    }

    public function latestLocation(TransportVehicle $vehicle)
    {
        return $this->gpsLogs->latestVehicleLocation($vehicle->id);
    }
}
