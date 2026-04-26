<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\StaffTransportAllocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StaffTransportAllocationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StaffTransportAllocation;

    public function create(array $attributes): StaffTransportAllocation;

    public function update(StaffTransportAllocation $allocation, array $attributes): StaffTransportAllocation;

    public function delete(StaffTransportAllocation $allocation): void;

    public function activeForStaff(int $staffId, ?int $ignoreId = null): ?StaffTransportAllocation;
}
