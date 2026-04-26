<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportGpsLog;
use App\Repositories\Contracts\Transport\TransportGpsLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportGpsLogRepository implements TransportGpsLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['vehicle_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('vehicle_id', $value))
            ->when($filters['transport_trip_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('transport_trip_id', $value))
            ->when($filters['driver_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('driver_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->where('recorded_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->where('recorded_at', '<=', $value))
            ->latest('recorded_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportGpsLog
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportGpsLog
    {
        $gpsLog = TransportGpsLog::create($attributes);

        return $this->findOrFail($gpsLog->id);
    }

    public function latestVehicleLocation(int $vehicleId): ?TransportGpsLog
    {
        return $this->query()
            ->where('vehicle_id', $vehicleId)
            ->latest('recorded_at')
            ->first();
    }

    protected function query(): Builder
    {
        return TransportGpsLog::query()->with(['vehicle', 'trip', 'driver']);
    }
}
