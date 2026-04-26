<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportVehicle;
use App\Repositories\Contracts\Transport\TransportVehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportVehicleRepository implements TransportVehicleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $vehicleQuery) use ($search): void {
                    $vehicleQuery->where('vehicle_no', 'like', "%{$search}%")
                        ->orWhere('registration_no', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($filters['vehicle_type'] ?? null, fn (Builder $query, string $value) => $query->where('vehicle_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportVehicle
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportVehicle
    {
        $vehicle = TransportVehicle::create($attributes);

        return $this->findOrFail($vehicle->id);
    }

    public function update(TransportVehicle $vehicle, array $attributes): TransportVehicle
    {
        $vehicle->update($attributes);

        return $this->findOrFail($vehicle->id);
    }

    public function delete(TransportVehicle $vehicle): void
    {
        $vehicle->delete();
    }

    protected function query(): Builder
    {
        return TransportVehicle::query()->withCount([
            'routeAssignments',
            'trips',
            'maintenanceLogs',
            'fuelLogs',
        ]);
    }
}
