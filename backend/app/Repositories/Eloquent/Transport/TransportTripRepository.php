<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportTrip;
use App\Repositories\Contracts\Transport\TransportTripRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportTripRepository implements TransportTripRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['route_vehicle_assignment_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('route_vehicle_assignment_id', $value))
            ->when($filters['route_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('route_id', $value))
            ->when($filters['vehicle_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('vehicle_id', $value))
            ->when($filters['driver_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('driver_id', $value))
            ->when($filters['trip_type'] ?? null, fn (Builder $query, string $value) => $query->where('trip_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('trip_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('trip_date', '<=', $value))
            ->latest('trip_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportTrip
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportTrip
    {
        $trip = TransportTrip::create($attributes);

        return $this->findOrFail($trip->id);
    }

    public function update(TransportTrip $trip, array $attributes): TransportTrip
    {
        $trip->update($attributes);

        return $this->findOrFail($trip->id);
    }

    public function delete(TransportTrip $trip): void
    {
        $trip->delete();
    }

    public function currentForAssignmentOnDate(int $assignmentId, string $tripDate, string $tripType, ?int $ignoreId = null): ?TransportTrip
    {
        return $this->query()
            ->where('route_vehicle_assignment_id', $assignmentId)
            ->whereDate('trip_date', $tripDate)
            ->where('trip_type', $tripType)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->first();
    }

    protected function query(): Builder
    {
        return TransportTrip::query()
            ->with(['routeAssignment', 'route', 'vehicle', 'driver', 'starter', 'completer'])
            ->withCount('tripLogs');
    }
}
