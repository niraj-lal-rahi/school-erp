<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\AttendancePeriodData;
use App\Models\Attendance\AttendancePeriod;
use Illuminate\Database\Eloquent\Collection;

interface AttendancePeriodRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(AttendancePeriodData $data): AttendancePeriod;

    public function update(AttendancePeriod $attendancePeriod, AttendancePeriodData $data): AttendancePeriod;

    public function delete(AttendancePeriod $attendancePeriod): void;
}
