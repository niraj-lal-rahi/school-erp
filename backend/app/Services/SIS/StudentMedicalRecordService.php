<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentMedicalRecordData;
use App\Models\Student;
use App\Models\StudentMedicalRecord;
use App\Repositories\Contracts\StudentMedicalRecordRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentMedicalRecordService
{
    public function __construct(
        protected StudentMedicalRecordRepositoryInterface $medicalRecords,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->medicalRecords->paginate($filters, $perPage);
    }

    public function show(StudentMedicalRecord $record): StudentMedicalRecord
    {
        return $this->medicalRecords->findOrFail($record->id);
    }

    public function create(Student $student, StudentMedicalRecordData $data, int $performedBy): StudentMedicalRecord
    {
        return DB::transaction(function () use ($student, $data, $performedBy): StudentMedicalRecord {
            return $this->medicalRecords->create($student, StudentMedicalRecordData::fromArray([
                ...$data->attributes,
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'created_by' => $performedBy,
                'updated_by' => $performedBy,
            ]));
        });
    }

    public function update(StudentMedicalRecord $record, StudentMedicalRecordData $data, int $performedBy): StudentMedicalRecord
    {
        return DB::transaction(function () use ($record, $data, $performedBy): StudentMedicalRecord {
            return $this->medicalRecords->update($record, StudentMedicalRecordData::fromArray([
                ...$data->attributes,
                'school_id' => $record->school_id,
                'student_id' => $record->student_id,
                'created_by' => $record->created_by,
                'updated_by' => $performedBy,
            ]));
        });
    }

    public function upsertForStudent(Student $student, StudentMedicalRecordData $data, int $performedBy): StudentMedicalRecord
    {
        $existing = $this->medicalRecords->latestForStudent($student);

        if ($existing) {
            return $this->update($existing, $data, $performedBy);
        }

        return $this->create($student, $data, $performedBy);
    }

    public function latestForStudent(Student $student): ?StudentMedicalRecord
    {
        return $this->medicalRecords->latestForStudent($student);
    }

    public function delete(StudentMedicalRecord $record): void
    {
        DB::transaction(fn (): bool => $record->delete());
    }
}
