<?php

namespace App\Repositories\Contracts\Examination;

use App\Models\Examination\ResultSubjectDetail;
use App\Models\Examination\StudentResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StudentResultRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StudentResult;

    public function create(array $attributes): StudentResult;

    public function update(StudentResult $studentResult, array $attributes): StudentResult;

    public function delete(StudentResult $studentResult): void;

    public function firstForExamStudent(int $examId, int $studentId): ?StudentResult;

    public function byExam(int $examId): Collection;

    public function createSubjectDetail(array $attributes): ResultSubjectDetail;

    public function deleteSubjectDetailsForResult(int $studentResultId): void;
}
