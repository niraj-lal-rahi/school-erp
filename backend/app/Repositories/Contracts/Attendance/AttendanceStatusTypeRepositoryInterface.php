<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\AttendanceStatusTypeData;
use App\Models\Attendance\AttendanceStatusType;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceStatusTypeRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(AttendanceStatusTypeData $data): AttendanceStatusType;

    public function update(AttendanceStatusType $attendanceStatusType, AttendanceStatusTypeData $data): AttendanceStatusType;

    public function delete(AttendanceStatusType $attendanceStatusType): void;
}
