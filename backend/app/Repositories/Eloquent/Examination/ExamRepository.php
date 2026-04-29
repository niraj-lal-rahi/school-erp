<?php

namespace App\Repositories\Eloquent\Examination;

use App\Models\Examination\Exam;
use App\Models\Examination\ExamSubject;
use App\Repositories\Contracts\Examination\ExamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExamRepository implements ExamRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $examQuery) use ($search): void {
                    $examQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('section_id', $value))
            ->when($filters['exam_type_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('exam_type_id', $value))
            ->when($filters['exam_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereKey($value))
            ->when($filters['result_status'] ?? null, fn (Builder $query, string $value) => $query->where('result_status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('start_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('end_date', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Exam
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): Exam
    {
        $exam = Exam::create($attributes);

        return $this->findOrFail($exam->id);
    }

    public function update(Exam $exam, array $attributes): Exam
    {
        $exam->update($attributes);

        return $this->findOrFail($exam->id);
    }

    public function delete(Exam $exam): void
    {
        $exam->delete();
    }

    public function createSubject(array $attributes): ExamSubject
    {
        $examSubject = ExamSubject::create($attributes);

        return ExamSubject::query()
            ->with(['exam', 'subject'])
            ->findOrFail($examSubject->id);
    }

    public function deleteSubject(ExamSubject $examSubject): void
    {
        $examSubject->delete();
    }

    public function subjectsForExam(int $examId): Collection
    {
        return ExamSubject::query()
            ->with(['subject'])
            ->where('exam_id', $examId)
            ->orderBy('id')
            ->get();
    }

    protected function query(): Builder
    {
        return Exam::query()
            ->with([
                'examType',
                'academicYear',
                'term',
                'schoolClass',
                'section',
                'creator',
            ])
            ->withCount([
                'examSubjects',
                'studentExamEnrollments',
                'examMarks',
                'studentResults',
            ]);
    }
}
