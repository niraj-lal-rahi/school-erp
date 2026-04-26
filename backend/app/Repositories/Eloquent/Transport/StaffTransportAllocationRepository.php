<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\StaffTransportAllocation;
use App\Repositories\Contracts\Transport\StaffTransportAllocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StaffTransportAllocationRepository implements StaffTransportAllocationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('staff_id', $value))
            ->when($filters['route_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('route_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('staff', function (Builder $staffQuery) use ($search): void {
                    $staffQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StaffTransportAllocation
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): StaffTransportAllocation
    {
        $allocation = StaffTransportAllocation::create($attributes);

        return $this->findOrFail($allocation->id);
    }

    public function update(StaffTransportAllocation $allocation, array $attributes): StaffTransportAllocation
    {
        $allocation->update($attributes);

        return $this->findOrFail($allocation->id);
    }

    public function delete(StaffTransportAllocation $allocation): void
    {
        $allocation->delete();
    }

    public function activeForStaff(int $staffId, ?int $ignoreId = null): ?StaffTransportAllocation
    {
        return $this->query()
            ->where('staff_id', $staffId)
            ->where('status', 'active')
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->latest('id')
            ->first();
    }

    protected function query(): Builder
    {
        return StaffTransportAllocation::query()
            ->with(['staff', 'route', 'routeAssignment', 'pickupStop', 'dropStop']);
    }
}
