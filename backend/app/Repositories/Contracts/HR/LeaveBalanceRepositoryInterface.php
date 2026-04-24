<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\LeaveBalanceData;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\Staff;
use Illuminate\Database\Eloquent\Collection;

interface LeaveBalanceRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function firstOrCreateForScope(Staff $staff, LeaveType $leaveType, ?int $academicYearId, array $defaults = []): LeaveBalance;

    public function update(LeaveBalance $balance, LeaveBalanceData $data): LeaveBalance;
}
