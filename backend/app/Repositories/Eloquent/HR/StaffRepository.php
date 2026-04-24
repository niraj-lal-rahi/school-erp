<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffData;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\StaffRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StaffRepository implements StaffRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $staffQuery) use ($search): void {
                    $staffQuery->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('alternate_phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'] ?? null, fn (Builder $query, int|string $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['designation_id'] ?? null, fn (Builder $query, int|string $designationId) => $query->where('designation_id', $designationId))
            ->when($filters['staff_type'] ?? null, fn (Builder $query, string $staffType) => $query->where('staff_type', $staffType))
            ->when($filters['employment_type'] ?? null, fn (Builder $query, string $employmentType) => $query->where('employment_type', $employmentType))
            ->when($filters['current_status'] ?? null, fn (Builder $query, string $status) => $query->where('current_status', $status))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Staff
    {
        return $this->query()->findOrFail($id);
    }

    public function create(StaffData $data): Staff
    {
        return Staff::create($data->attributes);
    }

    public function update(Staff $staff, StaffData $data): Staff
    {
        $staff->update($data->attributes);

        return $this->findOrFail($staff->id);
    }

    public function delete(Staff $staff): void
    {
        $staff->delete();
    }

    protected function query(): Builder
    {
        return Staff::query()->with(['user', 'department', 'designation']);
    }
}
