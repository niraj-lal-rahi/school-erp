<?php

namespace App\Services\HR;

use App\Services\Attendance\AttendanceHolidayService;
use App\Models\Attendance\AttendanceStatusType;
use App\DataTransferObjects\HR\StaffAttendanceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use App\Models\HR\StaffLeaveApplication;
use App\Repositories\Contracts\HR\StaffAttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffAttendanceService
{
    public function __construct(
        protected StaffAttendanceRepositoryInterface $attendance,
        protected AttendanceHolidayService $holidays,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendance->paginate($filters, $perPage);
    }

    public function show(StaffAttendance $attendance): StaffAttendance
    {
        return $this->attendance->findOrFail($attendance->id);
    }

    public function create(Staff $staff, StaffAttendanceData $data, int $markedBy): StaffAttendance
    {
        return DB::transaction(function () use ($staff, $data, $markedBy): StaffAttendance {
            return $this->attendance->create($staff, $this->normalizeDataForPersistence($staff, $data, $markedBy));
        });
    }

    public function update(StaffAttendance $attendance, StaffAttendanceData $data, int $markedBy): StaffAttendance
    {
        return DB::transaction(function () use ($attendance, $data, $markedBy): StaffAttendance {
            return $this->attendance->update($attendance, $this->normalizeDataForPersistence($attendance->staff, $data, $markedBy));
        });
    }

    public function delete(StaffAttendance $attendance): void
    {
        DB::transaction(function () use ($attendance): void {
            $attendance->delete();
        });
    }

    public function allForStaff(Staff $staff, array $filters = []): Collection
    {
        return $this->attendance->allForStaff($staff, $filters);
    }

    protected function normalizeDataForPersistence(Staff $staff, StaffAttendanceData $data, int $markedBy): StaffAttendanceData
    {
        $attributes = [
            ...$data->attributes,
            'marked_by' => $markedBy,
        ];

        $attendanceDate = $attributes['attendance_date'] ?? null;
        if (! $attendanceDate) {
            throw ValidationException::withMessages([
                'attendance_date' => ['Attendance date is required.'],
            ]);
        }

        $statusType = $this->resolveStatusType(
            $staff->school_id,
            $attributes['attendance_status_type_id'] ?? null,
            $attributes['attendance_status'] ?? null,
        );

        if ($this->hasApprovedLeave($staff, (string) $attendanceDate)) {
            $statusType = $this->resolveStatusType($staff->school_id, null, 'leave');
        }

        if ($this->isHoliday($staff, (string) $attendanceDate)) {
            throw ValidationException::withMessages([
                'attendance_date' => ['Attendance cannot be marked on a staff holiday.'],
            ]);
        }

        $attributes['attendance_status_type_id'] = $statusType->id;
        $attributes['attendance_status'] = strtolower($statusType->code);

        return StaffAttendanceData::fromArray($attributes);
    }

    protected function resolveStatusType(int $schoolId, int|string|null $statusTypeId, ?string $statusCode): AttendanceStatusType
    {
        $statusType = null;

        if ($statusTypeId) {
            $statusType = AttendanceStatusType::query()
                ->where('school_id', $schoolId)
                ->whereKey($statusTypeId)
                ->first();
        } elseif ($statusCode) {
            $statusType = AttendanceStatusType::query()
                ->where('school_id', $schoolId)
                ->where('code', strtoupper($statusCode))
                ->first();
        }

        if (! $statusType) {
            throw ValidationException::withMessages([
                'attendance_status' => ['A valid attendance status is required.'],
            ]);
        }

        return $statusType;
    }

    protected function hasApprovedLeave(Staff $staff, string $attendanceDate): bool
    {
        return StaffLeaveApplication::query()
            ->where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $attendanceDate)
            ->whereDate('end_date', '>=', $attendanceDate)
            ->exists();
    }

    protected function isHoliday(Staff $staff, string $attendanceDate): bool
    {
        $academicYearId = \App\Models\AcademicYear::query()
            ->where('school_id', $staff->school_id)
            ->whereDate('start_date', '<=', $attendanceDate)
            ->whereDate('end_date', '>=', $attendanceDate)
            ->value('id');

        if (! $academicYearId) {
            return false;
        }

        return $this->holidays->isHolidayForStaff($staff->school_id, (int) $academicYearId, $attendanceDate);
    }
}
