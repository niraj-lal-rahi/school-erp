<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffData;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\StaffRepositoryInterface;
use App\Support\Pagination\PaginationDefaults;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StaffRepository implements StaffRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->searchAcross($search, [
                    'staff.employee_code',
                    'staff.first_name',
                    'staff.middle_name',
                    'staff.last_name',
                    'staff.full_name',
                    'staff.email',
                    'staff.phone',
                    'staff.alternate_phone',
                ]);
            })
            ->when($filters['department_id'] ?? null, fn (Builder $query, int|string $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['designation_id'] ?? null, fn (Builder $query, int|string $designationId) => $query->where('designation_id', $designationId))
            ->when($filters['staff_type'] ?? null, fn (Builder $query, string $staffType) => $query->where('staff_type', $staffType))
            ->when($filters['employment_type'] ?? null, fn (Builder $query, string $employmentType) => $query->where('employment_type', $employmentType))
            ->whereStatus($filters['current_status'] ?? null, 'current_status')
            ->latest('id')
            ->paginate(PaginationDefaults::resolvePerPage($perPage));
    }

    public function findOrFail(int $id): Staff
    {
        return $this->detailQuery()->findOrFail($id);
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

    protected function baseQuery(): Builder
    {
        return Staff::query()->select([
            'staff.id',
            'staff.school_id',
            'staff.user_id',
            'staff.department_id',
            'staff.designation_id',
            'staff.employee_code',
            'staff.first_name',
            'staff.middle_name',
            'staff.last_name',
            'staff.full_name',
            'staff.gender',
            'staff.date_of_birth',
            'staff.email',
            'staff.phone',
            'staff.alternate_phone',
            'staff.photo_path',
            'staff.staff_type',
            'staff.employment_type',
            'staff.joining_date',
            'staff.leaving_date',
            'staff.current_status',
            'staff.qualification_summary',
            'staff.experience_years',
            'staff.address_line1',
            'staff.address_line2',
            'staff.city',
            'staff.state',
            'staff.country',
            'staff.postal_code',
            'staff.notes',
            'staff.created_at',
            'staff.updated_at',
        ]);
    }

    protected function listQuery(): Builder
    {
        return $this->baseQuery()->with([
            'user:id,name,email',
            'department:id,name,code',
            'designation:id,name,code',
        ]);
    }

    protected function detailQuery(): Builder
    {
        return $this->baseQuery()->with(['user', 'department', 'designation']);
    }
}
