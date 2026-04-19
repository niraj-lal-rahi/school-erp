<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\StudentMedicalRecordData;
use App\Models\Student;
use App\Models\StudentMedicalRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentMedicalRecordRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StudentMedicalRecord;

    public function create(Student $student, StudentMedicalRecordData $data): StudentMedicalRecord;

    public function update(StudentMedicalRecord $record, StudentMedicalRecordData $data): StudentMedicalRecord;

    public function delete(StudentMedicalRecord $record): void;

    public function latestForStudent(Student $student): ?StudentMedicalRecord;
}
