<?php

namespace App\Repositories\Eloquent\Examination;

use App\Models\Examination\ResultSubjectDetail;
use App\Models\Examination\StudentResult;
use App\Repositories\Contracts\Examination\StudentResultRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentResultRepository implements StudentResultRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $resultQuery) use ($search): void {
                    $resultQuery->whereHas('student', function (Builder $studentQuery) use ($search): void {
                        $studentQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('admission_no', 'like', "%{$search}%");
                    })->orWhereHas('exam', function (Builder $examQuery) use ($search): void {
                        $examQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('academic_year_id', $value)))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('class_id', $value)))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('section_id', $value)))
            ->when($filters['exam_type_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('exam', fn (Builder $examQuery) => $examQuery->where('exam_type_id', $value)))
            ->when($filters['exam_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('exam_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['result_status'] ?? null, fn (Builder $query, string $value) => $query->where('result_status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StudentResult
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): StudentResult
    {
        $studentResult = StudentResult::create($attributes);

        return $this->findOrFail($studentResult->id);
    }

    public function update(StudentResult $studentResult, array $attributes): StudentResult
    {
        $studentResult->update($attributes);

        return $this->findOrFail($studentResult->id);
    }

    public function delete(StudentResult $studentResult): void
    {
        $studentResult->delete();
    }

    public function firstForExamStudent(int $examId, int $studentId): ?StudentResult
    {
        return $this->query()
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function byExam(int $examId): Collection
    {
        return $this->query()
            ->where('exam_id', $examId)
            ->orderBy('rank')
            ->orderByDesc('percentage')
            ->orderBy('student_id')
            ->get();
    }

    public function createSubjectDetail(array $attributes): ResultSubjectDetail
    {
        $detail = ResultSubjectDetail::create($attributes);

        return ResultSubjectDetail::query()
            ->with(['subject', 'studentResult'])
            ->findOrFail($detail->id);
    }

    public function deleteSubjectDetailsForResult(int $studentResultId): void
    {
        ResultSubjectDetail::query()
            ->where('student_result_id', $studentResultId)
            ->delete();
    }

    protected function query(): Builder
    {
        return StudentResult::query()
            ->with([
                'exam',
                'student',
                'resultSubjectDetails.subject',
            ]);
    }
}
