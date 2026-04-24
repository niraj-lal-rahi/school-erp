<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\DepartmentData;
use App\Models\HR\Department;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return Department::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($departmentQuery) use ($search): void {
                    $departmentQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->withCount(['designations', 'staff'])
            ->orderBy('name')
            ->get();
    }

    public function create(DepartmentData $data): Department
    {
        return Department::create($data->attributes);
    }

    public function update(Department $department, DepartmentData $data): Department
    {
        $department->update($data->attributes);

        return $department->refresh()->loadCount(['designations', 'staff']);
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }
}
