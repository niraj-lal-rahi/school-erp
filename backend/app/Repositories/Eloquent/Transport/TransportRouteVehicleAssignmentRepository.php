<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportRouteVehicleAssignment;
use App\Repositories\Contracts\Transport\TransportRouteVehicleAssignmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportRouteVehicleAssignmentRepository implements TransportRouteVehicleAssignmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['route_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('route_id', $value))
            ->when($filters['vehicle_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('vehicle_id', $value))
            ->when($filters['driver_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('driver_id', $value))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportRouteVehicleAssignment
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportRouteVehicleAssignment
    {
        $assignment = TransportRouteVehicleAssignment::create($attributes);

        return $this->findOrFail($assignment->id);
    }

    public function update(TransportRouteVehicleAssignment $assignment, array $attributes): TransportRouteVehicleAssignment
    {
        $assignment->update($attributes);

        return $this->findOrFail($assignment->id);
    }

    public function delete(TransportRouteVehicleAssignment $assignment): void
    {
        $assignment->delete();
    }

    public function activeForRouteOnDate(int $routeId, string $date, ?int $ignoreId = null): ?TransportRouteVehicleAssignment
    {
        return $this->query()
            ->where('route_id', $routeId)
            ->where('status', 'active')
            ->whereDate('assigned_from', '<=', $date)
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('assigned_to')
                    ->orWhereDate('assigned_to', '>=', $date);
            })
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->first();
    }

    protected function query(): Builder
    {
        return TransportRouteVehicleAssignment::query()->with(['route', 'vehicle', 'driver', 'academicYear']);
    }
}
