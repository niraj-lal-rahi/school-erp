<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\StudentTransportAllocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentTransportAllocationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StudentTransportAllocation;

    public function create(array $attributes): StudentTransportAllocation;

    public function update(StudentTransportAllocation $allocation, array $attributes): StudentTransportAllocation;

    public function delete(StudentTransportAllocation $allocation): void;

    public function activeForStudent(int $studentId, int $academicYearId, ?int $ignoreId = null): ?StudentTransportAllocation;
}
