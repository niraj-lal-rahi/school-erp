<?php

namespace App\Repositories\Contracts\Examination;

use App\Models\Examination\ExamMark;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExamMarkRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ExamMark;

    public function create(array $attributes): ExamMark;

    public function update(ExamMark $examMark, array $attributes): ExamMark;

    public function delete(ExamMark $examMark): void;

    public function firstForExamStudentSubject(int $examId, int $studentId, int $subjectId): ?ExamMark;

    public function byExam(int $examId): Collection;
}
