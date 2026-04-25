<?php

namespace App\Services\Attendance;

use App\Models\AcademicYear;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\HR\StaffAttendance;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReportService
{
    public function studentSummary(array $filters = []): array
    {
        $query = $this->studentQuery($filters);

        return [
            'summary' => [
                'records_count' => (clone $query)->count(),
                'present_count' => $this->countStudentStatus($filters, 'PRESENT'),
                'absent_count' => $this->countStudentStatus($filters, 'ABSENT'),
                'late_count' => $this->countStudentStatus($filters, 'LATE'),
            ],
            'by_student' => $this->studentQuery($filters)
                ->selectRaw('student_id, COUNT(*) as total_records')
                ->with('student:id,full_name,admission_no')
                ->groupBy('student_id')
                ->orderByDesc('total_records')
                ->get()
                ->map(fn ($row) => [
                    'student_id' => $row->student_id,
                    'student' => $row->student ? [
                        'id' => $row->student->id,
                        'full_name' => $row->student->full_name,
                        'admission_no' => $row->student->admission_no,
                    ] : null,
                    'total_records' => (int) $row->total_records,
                ])
                ->values()
                ->all(),
        ];
    }

    public function staffSummary(array $filters = []): array
    {
        $query = $this->staffQuery($filters);

        return [
            'summary' => [
                'records_count' => (clone $query)->count(),
                'present_count' => $this->countStaffStatus($filters, 'PRESENT'),
                'absent_count' => $this->countStaffStatus($filters, 'ABSENT'),
                'leave_count' => $this->countStaffStatus($filters, 'LEAVE'),
            ],
            'by_staff' => $this->staffQuery($filters)
                ->selectRaw('staff_id, COUNT(*) as total_records')
                ->with('staff:id,employee_code,full_name')
                ->groupBy('staff_id')
                ->orderByDesc('total_records')
                ->get()
                ->map(fn ($row) => [
                    'staff_id' => $row->staff_id,
                    'staff' => $row->staff ? [
                        'id' => $row->staff->id,
                        'employee_code' => $row->staff->employee_code,
                        'full_name' => $row->staff->full_name,
                    ] : null,
                    'total_records' => (int) $row->total_records,
                ])
                ->values()
                ->all(),
        ];
    }

    public function classAttendance(array $filters = []): array
    {
        $rows = $this->studentQuery($filters)
            ->get()
            ->groupBy(fn ($record) => ($record->session?->school_class_id ?? '0').':'.($record->session?->section_id ?? '0'))
            ->map(function ($records) {
                $first = $records->first();

                return [
                    'school_class_id' => $first?->session?->school_class_id,
                    'section_id' => $first?->session?->section_id,
                    'school_class' => $first?->session?->schoolClass ? [
                        'id' => $first->session->schoolClass->id,
                        'name' => $first->session->schoolClass->name,
                        'code' => $first->session->schoolClass->code,
                    ] : null,
                    'section' => $first?->session?->section ? [
                        'id' => $first->session->section->id,
                        'name' => $first->session->section->name,
                        'code' => $first->session->section->code,
                    ] : null,
                    'total_records' => $records->count(),
                    'present_count' => $records->filter(fn ($record) => $record->attendanceStatus?->code === 'PRESENT')->count(),
                    'absent_count' => $records->filter(fn ($record) => $record->attendanceStatus?->code === 'ABSENT')->count(),
                ];
            })
            ->values()
            ->all();

        return [
            'rows' => $rows,
        ];
    }

    public function defaulters(array $filters = []): array
    {
        $threshold = (float) ($filters['threshold'] ?? 75);
        $summaries = app(AttendanceSummaryService::class)->refresh([
            'school_id' => $filters['school_id'],
            'academic_year_id' => $filters['academic_year_id'] ?? null,
            'user_type' => 'student',
        ]);

        return [
            'threshold' => $threshold,
            'rows' => $summaries
                ->filter(fn ($summary) => (float) $summary->percentage < $threshold)
                ->values()
                ->map(fn ($summary) => [
                    'user_id' => $summary->user_id,
                    'percentage' => (float) $summary->percentage,
                    'present_days' => $summary->present_days,
                    'total_days' => $summary->total_days,
                ])
                ->all(),
        ];
    }

    protected function studentQuery(array $filters = []): Builder
    {
        return StudentAttendanceRecord::query()
            ->with(['student', 'attendanceStatus', 'session.schoolClass', 'session.section'])
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('student_id', $id))
            ->when($filters['attendance_status'] ?? null, function (Builder $query, string $status): void {
                $query->whereHas('attendanceStatus', fn (Builder $statusQuery) => $statusQuery->where('code', strtoupper($status)));
            })
            ->when(($filters['academic_year_id'] ?? null) || ($filters['class_id'] ?? null) || ($filters['section_id'] ?? null) || ($filters['date_from'] ?? null) || ($filters['date_to'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('session', function (Builder $sessionQuery) use ($filters): void {
                    $sessionQuery
                        ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('academic_year_id', $id))
                        ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('school_class_id', $id))
                        ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('section_id', $id))
                        ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('attendance_date', '>=', $date))
                        ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('attendance_date', '<=', $date));
                });
            });
    }

    protected function staffQuery(array $filters = []): Builder
    {
        $academicYear = isset($filters['academic_year_id'])
            ? AcademicYear::query()->find($filters['academic_year_id'])
            : null;

        return StaffAttendance::query()
            ->with(['staff', 'attendanceStatusType'])
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('staff_id', $id))
            ->when($filters['attendance_status'] ?? null, function (Builder $query, string $status): void {
                $query->whereHas('attendanceStatusType', fn (Builder $statusQuery) => $statusQuery->where('code', strtoupper($status)));
            })
            ->when($academicYear, function (Builder $query) use ($academicYear): void {
                $query->whereDate('attendance_date', '>=', $academicYear->start_date)
                    ->whereDate('attendance_date', '<=', $academicYear->end_date);
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('attendance_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('attendance_date', '<=', $date));
    }

    protected function countStudentStatus(array $filters, string $code): int
    {
        return $this->studentQuery($filters)
            ->whereHas('attendanceStatus', fn (Builder $query) => $query->where('code', $code))
            ->count();
    }

    protected function countStaffStatus(array $filters, string $code): int
    {
        return $this->staffQuery($filters)
            ->whereHas('attendanceStatusType', fn (Builder $query) => $query->where('code', $code))
            ->count();
    }
}
