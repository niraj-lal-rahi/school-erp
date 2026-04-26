<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\VehicleMaintenanceLog;
use App\Repositories\Contracts\Transport\VehicleMaintenanceLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class VehicleMaintenanceLogRepository implements VehicleMaintenanceLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['vehicle_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('vehicle_id', $value))
            ->when($filters['maintenance_type'] ?? null, fn (Builder $query, string $value) => $query->where('maintenance_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('maintenance_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('maintenance_date', '<=', $value))
            ->latest('maintenance_date')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): VehicleMaintenanceLog
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): VehicleMaintenanceLog
    {
        $maintenanceLog = VehicleMaintenanceLog::create($attributes);

        return $this->findOrFail($maintenanceLog->id);
    }

    public function update(VehicleMaintenanceLog $maintenanceLog, array $attributes): VehicleMaintenanceLog
    {
        $maintenanceLog->update($attributes);

        return $this->findOrFail($maintenanceLog->id);
    }

    public function delete(VehicleMaintenanceLog $maintenanceLog): void
    {
        $maintenanceLog->delete();
    }

    protected function query(): Builder
    {
        return VehicleMaintenanceLog::query()->with(['vehicle', 'recorder']);
    }
}
