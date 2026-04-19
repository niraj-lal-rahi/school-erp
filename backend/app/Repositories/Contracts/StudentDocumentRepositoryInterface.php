<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentDocumentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StudentDocument;

    public function create(Student $student, array $attributes): StudentDocument;

    public function update(StudentDocument $document, array $attributes): StudentDocument;

    public function delete(StudentDocument $document): void;

    public function allForStudent(Student $student): Collection;
}
