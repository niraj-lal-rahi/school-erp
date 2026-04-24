<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\LeaveApplicationData;
use App\Models\HR\Staff;
use App\Models\HR\StaffLeaveApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StaffLeaveApplicationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StaffLeaveApplication;

    public function create(Staff $staff, LeaveApplicationData $data): StaffLeaveApplication;

    public function update(StaffLeaveApplication $application, LeaveApplicationData $data): StaffLeaveApplication;

    public function delete(StaffLeaveApplication $application): void;

    public function allForStaff(Staff $staff): Collection;
}
