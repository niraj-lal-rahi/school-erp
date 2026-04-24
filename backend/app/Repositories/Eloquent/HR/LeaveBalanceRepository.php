<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\LeaveBalanceData;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\LeaveBalanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LeaveBalanceRepository implements LeaveBalanceRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return LeaveBalance::query()
            ->with(['staff', 'leaveType', 'academicYear'])
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['leave_type_id'] ?? null, fn ($query, int|string $leaveTypeId) => $query->where('leave_type_id', $leaveTypeId))
            ->when($filters['academic_year_id'] ?? null, fn ($query, int|string $academicYearId) => $query->where('academic_year_id', $academicYearId))
            ->orderBy('id')
            ->get();
    }

    public function firstOrCreateForScope(Staff $staff, LeaveType $leaveType, ?int $academicYearId, array $defaults = []): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'school_id' => $staff->school_id,
                'staff_id' => $staff->id,
                'leave_type_id' => $leaveType->id,
                'academic_year_id' => $academicYearId,
            ],
            $defaults + [
                'allocated_days' => 0,
                'used_days' => 0,
                'remaining_days' => 0,
                'carried_forward_days' => 0,
            ],
        );
    }

    public function update(LeaveBalance $balance, LeaveBalanceData $data): LeaveBalance
    {
        $balance->update($data->attributes);

        return $balance->refresh()->load(['staff', 'leaveType', 'academicYear']);
    }
}
