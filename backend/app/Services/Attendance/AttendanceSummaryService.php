<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\AttendanceSummaryData;
use App\Models\AcademicYear;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\HR\StaffAttendance;
use App\Repositories\Contracts\Attendance\AttendanceSummaryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class AttendanceSummaryService
{
    public function __construct(
        protected AttendanceSummaryRepositoryInterface $summaries,
    ) {
    }

    public function all(array $filters = []): EloquentCollection
    {
        return $this->summaries->all($filters);
    }

    public function refresh(array $filters = []): EloquentCollection
    {
        $schoolId = (int) $filters['school_id'];
        $academicYearId = (int) ($filters['academic_year_id'] ?? AcademicYear::query()->where('is_current', true)->value('id'));

        $results = [];

        if (($filters['user_type'] ?? null) !== 'staff') {
            $studentRows = StudentAttendanceRecord::query()
                ->whereHas('session', fn ($query) => $query->where('academic_year_id', $academicYearId))
                ->with('attendanceStatus')
                ->get()
                ->groupBy('student_id');

            foreach ($studentRows as $studentId => $records) {
                $results[] = $this->summaries->upsert(AttendanceSummaryData::fromArray(
                    $this->buildSummaryPayload($schoolId, 'student', (int) $studentId, $academicYearId, $records)
                ));
            }
        }

        if (($filters['user_type'] ?? null) !== 'student') {
            $academicYear = AcademicYear::query()->find($academicYearId);
            $staffRows = StaffAttendance::query()
                ->with('attendanceStatusType')
                ->when($academicYear, function ($query) use ($academicYear): void {
                    $query->whereDate('attendance_date', '>=', $academicYear->start_date)
                        ->whereDate('attendance_date', '<=', $academicYear->end_date);
                })
                ->get()
                ->groupBy('staff_id');

            foreach ($staffRows as $staffId => $records) {
                $results[] = $this->summaries->upsert(AttendanceSummaryData::fromArray(
                    $this->buildSummaryPayload($schoolId, 'staff', (int) $staffId, $academicYearId, $records)
                ));
            }
        }

        return new EloquentCollection($results);
    }

    protected function buildSummaryPayload(int $schoolId, string $userType, int $userId, int $academicYearId, EloquentCollection $records): array
    {
        $total = $records->count();
        $present = $records->filter(function ($record): bool {
            return (bool) ($record->attendanceStatus?->is_present ?? $record->attendanceStatusType?->is_present);
        })->count();
        $absent = $records->filter(function ($record): bool {
            $code = $record->attendanceStatus?->code ?? $record->attendanceStatusType?->code;

            return $code === 'ABSENT';
        })->count();
        $leave = $records->filter(function ($record): bool {
            $code = $record->attendanceStatus?->code ?? $record->attendanceStatusType?->code;

            return $code === 'LEAVE';
        })->count();
        $late = $records->filter(function ($record): bool {
            $code = $record->attendanceStatus?->code ?? $record->attendanceStatusType?->code;

            return $code === 'LATE';
        })->count();

        return [
            'school_id' => $schoolId,
            'user_type' => $userType,
            'user_id' => $userId,
            'academic_year_id' => $academicYearId,
            'total_days' => $total,
            'present_days' => $present,
            'absent_days' => $absent,
            'leave_days' => $leave,
            'late_days' => $late,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'last_updated_at' => now(),
        ];
    }
}
