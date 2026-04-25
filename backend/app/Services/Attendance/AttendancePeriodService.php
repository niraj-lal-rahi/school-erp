<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\AttendancePeriodData;
use App\Models\Attendance\AttendancePeriod;
use App\Repositories\Contracts\Attendance\AttendancePeriodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttendancePeriodService
{
    public function __construct(
        protected AttendancePeriodRepositoryInterface $attendancePeriods,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->attendancePeriods->all($filters);
    }

    public function create(AttendancePeriodData $data): AttendancePeriod
    {
        return DB::transaction(fn (): AttendancePeriod => $this->attendancePeriods->create($data));
    }

    public function update(AttendancePeriod $attendancePeriod, AttendancePeriodData $data): AttendancePeriod
    {
        return DB::transaction(fn (): AttendancePeriod => $this->attendancePeriods->update($attendancePeriod, $data));
    }

    public function delete(AttendancePeriod $attendancePeriod): void
    {
        DB::transaction(function () use ($attendancePeriod): void {
            $this->attendancePeriods->delete($attendancePeriod);
        });
    }
}
