<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\VehicleFuelLog;
use App\Repositories\Contracts\Transport\VehicleFuelLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class VehicleFuelLogRepository implements VehicleFuelLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['vehicle_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('vehicle_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('fuel_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('fuel_date', '<=', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $fuelQuery) use ($search): void {
                    $fuelQuery->where('fuel_station', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->latest('fuel_date')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): VehicleFuelLog
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): VehicleFuelLog
    {
        $fuelLog = VehicleFuelLog::create($attributes);

        return $this->findOrFail($fuelLog->id);
    }

    public function update(VehicleFuelLog $fuelLog, array $attributes): VehicleFuelLog
    {
        $fuelLog->update($attributes);

        return $this->findOrFail($fuelLog->id);
    }

    public function delete(VehicleFuelLog $fuelLog): void
    {
        $fuelLog->delete();
    }

    protected function query(): Builder
    {
        return VehicleFuelLog::query()->with(['vehicle', 'recorder']);
    }
}
