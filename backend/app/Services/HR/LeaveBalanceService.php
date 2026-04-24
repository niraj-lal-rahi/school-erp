<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\LeaveBalanceData;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\LeaveBalanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function __construct(
        protected LeaveBalanceRepositoryInterface $balances,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->balances->all($filters);
    }

    public function ensureBalance(Staff $staff, LeaveType $leaveType, ?int $academicYearId): LeaveBalance
    {
        $quota = (float) ($leaveType->annual_quota ?? 0);

        return $this->balances->firstOrCreateForScope($staff, $leaveType, $academicYearId, [
            'allocated_days' => $quota,
            'used_days' => 0,
            'remaining_days' => $quota,
            'carried_forward_days' => 0,
        ]);
    }

    public function adjustUsage(LeaveBalance $balance, float $deltaDays): LeaveBalance
    {
        return DB::transaction(function () use ($balance, $deltaDays): LeaveBalance {
            $usedDays = max(0, (float) $balance->used_days + $deltaDays);
            $remainingDays = max(0, (float) $balance->allocated_days + (float) $balance->carried_forward_days - $usedDays);

            return $this->balances->update($balance, LeaveBalanceData::fromArray([
                'allocated_days' => $balance->allocated_days,
                'used_days' => $usedDays,
                'remaining_days' => $remainingDays,
                'carried_forward_days' => $balance->carried_forward_days,
            ]));
        });
    }
}
