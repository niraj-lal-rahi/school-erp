<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\AttendanceStatusTypeData;
use App\Models\Attendance\AttendanceStatusType;
use App\Repositories\Contracts\Attendance\AttendanceStatusTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceStatusTypeService
{
    public function __construct(
        protected AttendanceStatusTypeRepositoryInterface $attendanceStatusTypes,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->attendanceStatusTypes->all($filters);
    }

    public function create(AttendanceStatusTypeData $data): AttendanceStatusType
    {
        return DB::transaction(fn (): AttendanceStatusType => $this->attendanceStatusTypes->create($data));
    }

    public function update(AttendanceStatusType $attendanceStatusType, AttendanceStatusTypeData $data): AttendanceStatusType
    {
        return DB::transaction(fn (): AttendanceStatusType => $this->attendanceStatusTypes->update($attendanceStatusType, $data));
    }

    public function delete(AttendanceStatusType $attendanceStatusType): void
    {
        DB::transaction(function () use ($attendanceStatusType): void {
            $this->attendanceStatusTypes->delete($attendanceStatusType);
        });
    }
}
