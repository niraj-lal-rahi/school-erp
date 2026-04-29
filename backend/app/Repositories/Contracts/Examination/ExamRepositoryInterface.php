<?php

namespace App\Repositories\Contracts\Examination;

use App\Models\Examination\Exam;
use App\Models\Examination\ExamSubject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExamRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Exam;

    public function create(array $attributes): Exam;

    public function update(Exam $exam, array $attributes): Exam;

    public function delete(Exam $exam): void;

    public function createSubject(array $attributes): ExamSubject;

    public function deleteSubject(ExamSubject $examSubject): void;

    public function subjectsForExam(int $examId): Collection;
}
