<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportDriver;
use App\Repositories\Contracts\Transport\TransportDriverRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportDriverRepository implements TransportDriverRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $driverQuery) use ($search): void {
                    $driverQuery->where('driver_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('license_no', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('staff_id', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportDriver
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportDriver
    {
        $driver = TransportDriver::create($attributes);

        return $this->findOrFail($driver->id);
    }

    public function update(TransportDriver $driver, array $attributes): TransportDriver
    {
        $driver->update($attributes);

        return $this->findOrFail($driver->id);
    }

    public function delete(TransportDriver $driver): void
    {
        $driver->delete();
    }

    protected function query(): Builder
    {
        return TransportDriver::query()->with(['staff'])->withCount(['routeAssignments', 'trips']);
    }
}
