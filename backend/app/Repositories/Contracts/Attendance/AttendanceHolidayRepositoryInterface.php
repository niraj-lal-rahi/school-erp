<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\AttendanceHolidayData;
use App\Models\Attendance\AttendanceHoliday;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceHolidayRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(AttendanceHolidayData $data): AttendanceHoliday;

    public function update(AttendanceHoliday $holiday, AttendanceHolidayData $data): AttendanceHoliday;

    public function delete(AttendanceHoliday $holiday): void;
}
