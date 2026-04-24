<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\LeaveApplicationData;
use App\Enums\HR\LeaveApplicationStatus;
use App\Models\AcademicYear;
use App\Models\HR\LeaveType;
use App\Models\HR\Staff;
use App\Models\HR\StaffLeaveApplication;
use App\Repositories\Contracts\HR\StaffLeaveApplicationRepositoryInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffLeaveApplicationService
{
    public function __construct(
        protected StaffLeaveApplicationRepositoryInterface $applications,
        protected LeaveBalanceService $balances,
        protected StaffAttendanceService $attendance,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->applications->paginate($filters, $perPage);
    }

    public function show(StaffLeaveApplication $application): StaffLeaveApplication
    {
        return $this->applications->findOrFail($application->id);
    }

    public function create(Staff $staff, LeaveApplicationData $data): StaffLeaveApplication
    {
        return DB::transaction(function () use ($staff, $data): StaffLeaveApplication {
            $attributes = $data->attributes;
            $attributes['total_days'] = $this->calculateTotalDays($attributes['start_date'], $attributes['end_date']);
            $attributes['status'] = $attributes['status'] ?? LeaveApplicationStatus::Draft->value;

            return $this->applications->create($staff, LeaveApplicationData::fromArray($attributes));
        });
    }

    public function update(StaffLeaveApplication $application, LeaveApplicationData $data): StaffLeaveApplication
    {
        return DB::transaction(function () use ($application, $data): StaffLeaveApplication {
            $attributes = $data->attributes;

            if (isset($attributes['start_date'], $attributes['end_date'])) {
                $attributes['total_days'] = $this->calculateTotalDays($attributes['start_date'], $attributes['end_date']);
            }

            return $this->applications->update($application, LeaveApplicationData::fromArray($attributes));
        });
    }

    public function delete(StaffLeaveApplication $application): void
    {
        DB::transaction(fn (): bool => $application->delete());
    }

    public function submit(StaffLeaveApplication $application): StaffLeaveApplication
    {
        return $this->update($application, LeaveApplicationData::fromArray([
            'status' => LeaveApplicationStatus::Submitted->value,
        ]));
    }

    public function approve(StaffLeaveApplication $application, int $reviewedBy, ?string $remarks = null): StaffLeaveApplication
    {
        return DB::transaction(function () use ($application, $reviewedBy, $remarks): StaffLeaveApplication {
            /** @var LeaveType $leaveType */
            $leaveType = $application->leaveType()->firstOrFail();
            $academicYearId = $this->resolveAcademicYearId($application->staff, $application->start_date?->toDateString());
            $balance = $this->balances->ensureBalance($application->staff, $leaveType, $academicYearId);
            $this->balances->adjustUsage($balance, (float) $application->total_days);

            $approved = $this->applications->update($application, LeaveApplicationData::fromArray([
                'status' => LeaveApplicationStatus::Approved->value,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
                'review_remarks' => $remarks,
            ]));

            foreach (CarbonPeriod::create($approved->start_date, $approved->end_date) as $date) {
                $alreadyMarked = $approved->staff->attendanceRecords()
                    ->whereDate('attendance_date', $date->toDateString())
                    ->exists();

                if ($alreadyMarked) {
                    continue;
                }

                $this->attendance->create($approved->staff, \App\DataTransferObjects\HR\StaffAttendanceData::fromArray([
                    'attendance_date' => $date->toDateString(),
                    'attendance_status' => 'leave',
                    'source' => 'manual',
                    'remarks' => 'Auto-created from approved leave application.',
                ]), $reviewedBy);
            }

            return $approved;
        });
    }

    public function reject(StaffLeaveApplication $application, int $reviewedBy, ?string $remarks = null): StaffLeaveApplication
    {
        return $this->applications->update($application, LeaveApplicationData::fromArray([
            'status' => LeaveApplicationStatus::Rejected->value,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_remarks' => $remarks,
        ]));
    }

    public function cancel(StaffLeaveApplication $application, int $reviewedBy, ?string $remarks = null): StaffLeaveApplication
    {
        return DB::transaction(function () use ($application, $reviewedBy, $remarks): StaffLeaveApplication {
            if ($application->status === LeaveApplicationStatus::Approved->value) {
                $academicYearId = $this->resolveAcademicYearId($application->staff, $application->start_date?->toDateString());
                $balance = $this->balances->ensureBalance($application->staff, $application->leaveType, $academicYearId);
                $this->balances->adjustUsage($balance, -1 * (float) $application->total_days);
            }

            return $this->applications->update($application, LeaveApplicationData::fromArray([
                'status' => LeaveApplicationStatus::Cancelled->value,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
                'review_remarks' => $remarks,
            ]));
        });
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->applications->allForStaff($staff);
    }

    protected function calculateTotalDays(string $startDate, string $endDate): float
    {
        return (float) CarbonPeriod::create($startDate, $endDate)->count();
    }

    protected function resolveAcademicYearId(Staff $staff, ?string $date): ?int
    {
        if (! $date) {
            return null;
        }

        return AcademicYear::query()
            ->where('school_id', $staff->school_id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->value('id');
    }
}
