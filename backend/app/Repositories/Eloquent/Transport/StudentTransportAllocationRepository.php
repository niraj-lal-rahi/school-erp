<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\StudentTransportAllocation;
use App\Repositories\Contracts\Transport\StudentTransportAllocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StudentTransportAllocationRepository implements StudentTransportAllocationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['route_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('route_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($search): void {
                    $studentQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%")
                        ->orWhere('roll_no', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StudentTransportAllocation
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): StudentTransportAllocation
    {
        $allocation = StudentTransportAllocation::create($attributes);

        return $this->findOrFail($allocation->id);
    }

    public function update(StudentTransportAllocation $allocation, array $attributes): StudentTransportAllocation
    {
        $allocation->update($attributes);

        return $this->findOrFail($allocation->id);
    }

    public function delete(StudentTransportAllocation $allocation): void
    {
        $allocation->delete();
    }

    public function activeForStudent(int $studentId, int $academicYearId, ?int $ignoreId = null): ?StudentTransportAllocation
    {
        return $this->query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->latest('id')
            ->first();
    }

    protected function query(): Builder
    {
        return StudentTransportAllocation::query()
            ->with(['student', 'academicYear', 'route', 'routeAssignment', 'pickupStop', 'dropStop']);
    }
}
