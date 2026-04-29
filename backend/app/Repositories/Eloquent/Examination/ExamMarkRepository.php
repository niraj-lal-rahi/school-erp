<?php

namespace App\Repositories\Eloquent\Examination;

use App\Models\Examination\ExamMark;
use App\Repositories\Contracts\Examination\ExamMarkRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExamMarkRepository implements ExamMarkRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $markQuery) use ($search): void {
                    $markQuery->whereHas('student', function (Builder $studentQuery) use ($search): void {
                        $studentQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('admission_no', 'like', "%{$search}%");
                    })->orWhereHas('subject', function (Builder $subjectQuery) use ($search): void {
                        $subjectQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })->orWhereHas('exam', function (Builder $examQuery) use ($search): void {
                        $examQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('academic_year_id', $value)))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('class_id', $value)))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('section_id', $value)))
            ->when($filters['exam_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('exam_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['subject_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('subject_id', $value))
            ->when(array_key_exists('is_absent', $filters), fn (Builder $query) => $query->where('is_absent', (bool) $filters['is_absent']))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ExamMark
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ExamMark
    {
        $examMark = ExamMark::create($attributes);

        return $this->findOrFail($examMark->id);
    }

    public function update(ExamMark $examMark, array $attributes): ExamMark
    {
        $examMark->update($attributes);

        return $this->findOrFail($examMark->id);
    }

    public function delete(ExamMark $examMark): void
    {
        $examMark->delete();
    }

    public function firstForExamStudentSubject(int $examId, int $studentId, int $subjectId): ?ExamMark
    {
        return $this->query()
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->first();
    }

    public function byExam(int $examId): Collection
    {
        return $this->query()
            ->where('exam_id', $examId)
            ->orderBy('student_id')
            ->orderBy('subject_id')
            ->get();
    }

    protected function query(): Builder
    {
        return ExamMark::query()
            ->with([
                'exam',
                'student',
                'subject',
                'evaluator',
            ]);
    }
}
