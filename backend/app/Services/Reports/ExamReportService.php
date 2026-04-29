<?php

namespace App\Services\Reports;

use App\Models\Examination\ResultSubjectDetail;
use App\Models\Examination\StudentResult;
use Illuminate\Database\Eloquent\Builder;

class ExamReportService
{
    public function subjectAverages(array $filters = []): array
    {
        $rows = $this->subjectDetailQuery($filters)
            ->selectRaw('subject_id, AVG(obtained_marks) as average_marks, AVG((obtained_marks / NULLIF(max_marks, 0)) * 100) as average_percentage, COUNT(*) as records_count')
            ->with('subject:id,name,code')
            ->groupBy('subject_id')
            ->orderByDesc('average_percentage')
            ->get()
            ->map(fn ($row) => [
                'subject_id' => $row->subject_id,
                'subject' => $row->subject ? [
                    'id' => $row->subject->id,
                    'name' => $row->subject->name,
                    'code' => $row->subject->code,
                ] : null,
                'average_marks' => round((float) $row->average_marks, 2),
                'average_percentage' => round((float) $row->average_percentage, 2),
                'records_count' => (int) $row->records_count,
            ])
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function passFailRatios(array $filters = []): array
    {
        $query = $this->resultQuery($filters);
        $total = (clone $query)->count();
        $pass = (clone $query)->where('result_status', 'pass')->count();
        $fail = (clone $query)->where('result_status', 'fail')->count();
        $absent = (clone $query)->where('result_status', 'absent')->count();
        $withheld = (clone $query)->where('result_status', 'withheld')->count();

        return [
            'summary' => [
                'total_results' => $total,
                'pass_count' => $pass,
                'fail_count' => $fail,
                'absent_count' => $absent,
                'withheld_count' => $withheld,
                'pass_percentage' => $total > 0 ? round(($pass / $total) * 100, 2) : 0.0,
            ],
            'rows' => [
                ['label' => 'Pass', 'count' => $pass],
                ['label' => 'Fail', 'count' => $fail],
                ['label' => 'Absent', 'count' => $absent],
                ['label' => 'Withheld', 'count' => $withheld],
            ],
        ];
    }

    public function toppers(array $filters = []): array
    {
        $rows = $this->resultQuery($filters)
            ->where('result_status', 'pass')
            ->with(['student:id,full_name,admission_no', 'exam:id,name,class_id,section_id'])
            ->orderByRaw('COALESCE(rank, 999999) asc')
            ->orderByDesc('percentage')
            ->limit((int) ($filters['limit'] ?? 10))
            ->get()
            ->map(fn (StudentResult $result) => [
                'id' => $result->id,
                'rank' => $result->rank,
                'percentage' => round((float) $result->percentage, 2),
                'grade' => $result->grade,
                'gpa' => $result->gpa !== null ? round((float) $result->gpa, 2) : null,
                'student' => $result->student ? [
                    'id' => $result->student->id,
                    'full_name' => $result->student->full_name,
                    'admission_no' => $result->student->admission_no,
                ] : null,
                'exam' => $result->exam ? [
                    'id' => $result->exam->id,
                    'name' => $result->exam->name,
                    'class_id' => $result->exam->class_id,
                    'section_id' => $result->exam->section_id,
                ] : null,
            ])
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function trends(array $filters = []): array
    {
        $rows = $this->resultQuery($filters)
            ->join('exams', 'student_results.exam_id', '=', 'exams.id')
            ->selectRaw('student_results.exam_id, exams.name as exam_name, exams.start_date, AVG(student_results.percentage) as average_percentage, SUM(CASE WHEN student_results.result_status = "pass" THEN 1 ELSE 0 END) as pass_count, COUNT(*) as total_results')
            ->groupBy('student_results.exam_id', 'exams.name', 'exams.start_date')
            ->orderBy('exams.start_date')
            ->get()
            ->map(fn ($row) => [
                'exam_id' => $row->exam_id,
                'exam_name' => $row->exam_name,
                'start_date' => $row->start_date,
                'average_percentage' => round((float) $row->average_percentage, 2),
                'pass_count' => (int) $row->pass_count,
                'total_results' => (int) $row->total_results,
                'pass_rate' => (int) $row->total_results > 0 ? round(((int) $row->pass_count / (int) $row->total_results) * 100, 2) : 0.0,
            ])
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function overview(array $filters = []): array
    {
        $query = $this->resultQuery($filters);

        return [
            'summary' => [
                'results_count' => (clone $query)->count(),
                'average_percentage' => round((float) ((clone $query)->avg('percentage') ?? 0), 2),
                'highest_percentage' => round((float) ((clone $query)->max('percentage') ?? 0), 2),
            ],
            'pass_fail' => $this->passFailRatios($filters),
        ];
    }

    protected function resultQuery(array $filters = []): Builder
    {
        return StudentResult::query()
            ->when($filters['exam_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('exam_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['result_status'] ?? null, fn (Builder $query, string $value) => $query->where('result_status', $value))
            ->when(($filters['academic_year_id'] ?? null) || ($filters['class_id'] ?? null) || ($filters['section_id'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('exam', function (Builder $examQuery) use ($filters): void {
                    $examQuery
                        ->when($filters['academic_year_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('academic_year_id', $value))
                        ->when($filters['class_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('class_id', $value))
                        ->when($filters['section_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('section_id', $value));
                });
            });
    }

    protected function subjectDetailQuery(array $filters = []): Builder
    {
        return ResultSubjectDetail::query()
            ->when($filters['subject_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('subject_id', $value))
            ->when(($filters['exam_id'] ?? null) || ($filters['academic_year_id'] ?? null) || ($filters['class_id'] ?? null) || ($filters['section_id'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('studentResult.exam', function (Builder $examQuery) use ($filters): void {
                    $examQuery
                        ->when($filters['exam_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('id', $value))
                        ->when($filters['academic_year_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('academic_year_id', $value))
                        ->when($filters['class_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('class_id', $value))
                        ->when($filters['section_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('section_id', $value));
                });
            });
    }
}
