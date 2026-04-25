<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffAttendanceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use App\Repositories\Contracts\HR\StaffAttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StaffAttendanceRepository implements StaffAttendanceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['attendance_status'] ?? null, fn (Builder $query, string $status) => $query->where('attendance_status', $status))
            ->when($filters['attendance_status_type_id'] ?? null, fn (Builder $query, int|string $statusTypeId) => $query->where('attendance_status_type_id', $statusTypeId))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom) => $query->whereDate('attendance_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo) => $query->whereDate('attendance_date', '<=', $dateTo))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('staff', function (Builder $staffQuery) use ($search): void {
                    $staffQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StaffAttendance
    {
        return $this->query()->findOrFail($id);
    }

    public function create(Staff $staff, StaffAttendanceData $data): StaffAttendance
    {
        $attendance = $staff->attendanceRecords()->create($data->attributes + ['school_id' => $staff->school_id]);

        return $this->findOrFail($attendance->id);
    }

    public function update(StaffAttendance $attendance, StaffAttendanceData $data): StaffAttendance
    {
        $attendance->update($data->attributes);

        return $this->findOrFail($attendance->id);
    }

    public function delete(StaffAttendance $attendance): void
    {
        $attendance->delete();
    }

    public function allForStaff(Staff $staff, array $filters = []): Collection
    {
        return $this->query()
            ->where('staff_id', $staff->id)
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom) => $query->whereDate('attendance_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo) => $query->whereDate('attendance_date', '<=', $dateTo))
            ->when($filters['attendance_status'] ?? null, fn (Builder $query, string $status) => $query->where('attendance_status', $status))
            ->when($filters['attendance_status_type_id'] ?? null, fn (Builder $query, int|string $statusTypeId) => $query->where('attendance_status_type_id', $statusTypeId))
            ->orderByDesc('attendance_date')
            ->get();
    }

    protected function query(): Builder
    {
        return StaffAttendance::query()->with(['staff', 'marker', 'attendanceStatusType']);
    }
}
