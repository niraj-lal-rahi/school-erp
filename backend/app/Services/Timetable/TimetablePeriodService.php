<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Attendance\AttendancePeriodData;
use App\Models\Attendance\AttendancePeriod;
use App\Repositories\Contracts\Attendance\AttendancePeriodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TimetablePeriodService
{
    public function __construct(
        protected AttendancePeriodRepositoryInterface $periods,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->periods->all($filters);
    }

    public function create(AttendancePeriodData $data): AttendancePeriod
    {
        return DB::transaction(fn (): AttendancePeriod => $this->periods->create($data));
    }

    public function update(AttendancePeriod $period, AttendancePeriodData $data): AttendancePeriod
    {
        return DB::transaction(fn (): AttendancePeriod => $this->periods->update($period, $data));
    }

    public function delete(AttendancePeriod $period): void
    {
        DB::transaction(function () use ($period): void {
            $this->periods->delete($period);
        });
    }
}
