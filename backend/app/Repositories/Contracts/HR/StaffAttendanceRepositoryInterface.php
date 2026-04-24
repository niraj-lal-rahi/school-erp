<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffAttendanceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StaffAttendanceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StaffAttendance;

    public function create(Staff $staff, StaffAttendanceData $data): StaffAttendance;

    public function update(StaffAttendance $attendance, StaffAttendanceData $data): StaffAttendance;

    public function delete(StaffAttendance $attendance): void;

    public function allForStaff(Staff $staff, array $filters = []): Collection;
}
