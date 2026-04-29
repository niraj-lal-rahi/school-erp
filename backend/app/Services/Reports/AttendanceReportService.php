<?php

namespace App\Services\Reports;

use App\Services\Attendance\AttendanceReportService as SourceAttendanceReportService;

class AttendanceReportService
{
    public function __construct(
        protected SourceAttendanceReportService $source,
    ) {
    }

    public function studentAttendancePercentage(array $filters = []): array
    {
        $payload = $this->source->studentSummary($filters);
        $rows = collect($payload['by_student'] ?? [])
            ->map(function (array $row) use ($filters): array {
                $studentFilters = array_merge($filters, ['student_id' => $row['student_id']]);
                $summary = $this->source->studentSummary($studentFilters)['summary'] ?? [];
                $total = max(1, (int) ($summary['records_count'] ?? 0));
                $present = (int) ($summary['present_count'] ?? 0);

                return [
                    ...$row,
                    'attendance_percentage' => round(($present / $total) * 100, 2),
                    'present_count' => $present,
                    'absent_count' => (int) ($summary['absent_count'] ?? 0),
                    'late_count' => (int) ($summary['late_count'] ?? 0),
                ];
            })
            ->values()
            ->all();

        return [
            'summary' => $payload['summary'] ?? [],
            'rows' => $rows,
        ];
    }

    public function classWiseSummaries(array $filters = []): array
    {
        return $this->source->classAttendance($filters);
    }

    public function defaulters(array $filters = []): array
    {
        return $this->source->defaulters($filters);
    }

    public function staffSummary(array $filters = []): array
    {
        return $this->source->staffSummary($filters);
    }

    public function overview(array $filters = []): array
    {
        $student = $this->source->studentSummary($filters);
        $staff = $this->source->staffSummary($filters);

        return [
            'student_summary' => $student['summary'] ?? [],
            'staff_summary' => $staff['summary'] ?? [],
            'class_rows' => $this->source->classAttendance($filters)['rows'] ?? [],
        ];
    }
}
