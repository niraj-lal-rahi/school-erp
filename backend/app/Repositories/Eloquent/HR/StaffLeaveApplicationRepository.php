<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\LeaveApplicationData;
use App\Models\HR\Staff;
use App\Models\HR\StaffLeaveApplication;
use App\Repositories\Contracts\HR\StaffLeaveApplicationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StaffLeaveApplicationRepository implements StaffLeaveApplicationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['leave_type_id'] ?? null, fn (Builder $query, int|string $leaveTypeId) => $query->where('leave_type_id', $leaveTypeId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom) => $query->whereDate('start_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo) => $query->whereDate('end_date', '<=', $dateTo))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $leaveQuery) use ($search): void {
                    $leaveQuery->where('reason', 'like', "%{$search}%")
                        ->orWhereHas('staff', function (Builder $staffQuery) use ($search): void {
                            $staffQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('employee_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('leaveType', function (Builder $typeQuery) use ($search): void {
                            $typeQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StaffLeaveApplication
    {
        return $this->query()->findOrFail($id);
    }

    public function create(Staff $staff, LeaveApplicationData $data): StaffLeaveApplication
    {
        return $staff->leaveApplications()->create($data->attributes + ['school_id' => $staff->school_id]);
    }

    public function update(StaffLeaveApplication $application, LeaveApplicationData $data): StaffLeaveApplication
    {
        $application->update($data->attributes);

        return $this->findOrFail($application->id);
    }

    public function delete(StaffLeaveApplication $application): void
    {
        $application->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->query()->where('staff_id', $staff->id)->get();
    }

    protected function query(): Builder
    {
        return StaffLeaveApplication::query()->with(['staff', 'leaveType', 'reviewer']);
    }
}
