<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffAttendanceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use App\Repositories\Contracts\HR\StaffAttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffAttendanceService
{
    public function __construct(
        protected StaffAttendanceRepositoryInterface $attendance,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendance->paginate($filters, $perPage);
    }

    public function show(StaffAttendance $attendance): StaffAttendance
    {
        return $this->attendance->findOrFail($attendance->id);
    }

    public function create(Staff $staff, StaffAttendanceData $data, int $markedBy): StaffAttendance
    {
        return DB::transaction(fn (): StaffAttendance => $this->attendance->create($staff, StaffAttendanceData::fromArray([
            ...$data->attributes,
            'marked_by' => $markedBy,
        ])));
    }

    public function update(StaffAttendance $attendance, StaffAttendanceData $data, int $markedBy): StaffAttendance
    {
        return DB::transaction(fn (): StaffAttendance => $this->attendance->update($attendance, StaffAttendanceData::fromArray([
            ...$data->attributes,
            'marked_by' => $markedBy,
        ])));
    }

    public function delete(StaffAttendance $attendance): void
    {
        DB::transaction(fn (): bool => $attendance->delete());
    }

    public function allForStaff(Staff $staff, array $filters = []): Collection
    {
        return $this->attendance->allForStaff($staff, $filters);
    }
}
