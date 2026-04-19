<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\StudentData;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Student;

    public function create(StudentData $data): Student;

    public function update(Student $student, StudentData $data): Student;

    public function delete(Student $student): void;

    public function assignGuardians(Student $student, array $guardianPivotData, bool $detaching = false): Student;

    public function removeGuardian(Student $student, int $guardianId): Student;

    public function guardians(Student $student): Collection;

    public function createDocument(Student $student, array $attributes): StudentDocument;
}
