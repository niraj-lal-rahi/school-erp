<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\StudentData;
use App\DataTransferObjects\SIS\StudentDocumentData;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(StudentData $data): Student;

    public function update(Student $student, StudentData $data): Student;

    public function delete(Student $student): void;

    public function createDocument(Student $student, array $attributes): StudentDocument;
}
