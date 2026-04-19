<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentData;
use App\DataTransferObjects\SIS\StudentDocumentData;
use App\Events\SIS\StudentCreated;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function __construct(
        protected StudentRepositoryInterface $students,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->students->paginate($filters, $perPage);
    }

    public function show(Student $student): Student
    {
        return $this->students->findOrFail($student->id);
    }

    public function create(StudentData $data, int $performedBy): Student
    {
        return DB::transaction(function () use ($data, $performedBy): Student {
            $student = $this->students->create(
                StudentData::fromArray([
                    ...$data->studentAttributes(),
                    'created_by' => $performedBy,
                    'updated_by' => $performedBy,
                    'guardians' => $data->guardians,
                    'enrollment' => $data->enrollment,
                    'admission' => $data->admission,
                ])
            );

            event(new StudentCreated($student));

            return $student;
        });
    }

    public function update(Student $student, StudentData $data, int $performedBy): Student
    {
        return DB::transaction(function () use ($student, $data, $performedBy): Student {
            return $this->students->update(
                $student,
                StudentData::fromArray([
                    ...$data->studentAttributes(),
                    'created_by' => $student->created_by,
                    'updated_by' => $performedBy,
                    'guardians' => $data->guardians,
                    'enrollment' => $data->enrollment,
                    'admission' => $data->admission,
                ])
            );
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student): void {
            $this->students->delete($student);
        });
    }

    public function assignGuardian(Student $student, array $payload): Student
    {
        return DB::transaction(function () use ($student, $payload): Student {
            if (($payload['is_primary'] ?? false) === true) {
                $student->guardians()
                    ->newPivotStatement()
                    ->where('student_id', $student->id)
                    ->update(['is_primary' => false]);
            }

            return $this->students->assignGuardians($student, [
                $payload['guardian_id'] => [
                    'school_id' => $student->school_id,
                    'relationship' => $payload['relationship'] ?? null,
                    'relationship_label' => $payload['relationship_label'] ?? $payload['relationship'] ?? null,
                    'is_primary' => $payload['is_primary'] ?? false,
                    'is_emergency_contact' => $payload['is_emergency_contact'] ?? false,
                    'pickup_authorized' => $payload['pickup_authorized'] ?? true,
                    'financial_responsibility_percentage' => $payload['financial_responsibility_percentage'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                ],
            ]);
        });
    }

    public function removeGuardian(Student $student, int $guardianId): Student
    {
        return DB::transaction(fn (): Student => $this->students->removeGuardian($student, $guardianId));
    }

    public function guardians(Student $student): Collection
    {
        return $this->students->guardians($student);
    }

    public function uploadDocument(Student $student, StudentDocumentData $data, int $uploadedBy): StudentDocument
    {
        return DB::transaction(function () use ($student, $data, $uploadedBy): StudentDocument {
            $disk = (string) config('filesystems.default', 'local');
            $path = Storage::disk($disk)->putFile(
                'students/'.$student->id.'/documents',
                $data->file
            );

            return $this->students->createDocument($student, [
                'school_id' => $student->school_id,
                'uploaded_by' => $uploadedBy,
                'document_type' => $data->documentType,
                'title' => $data->title,
                'disk' => $disk,
                'file_path' => $path,
                'metadata' => [
                    'original_name' => $data->file->getClientOriginalName(),
                    'mime_type' => $data->file->getMimeType(),
                    'size' => $data->file->getSize(),
                    ...$data->metadata,
                ],
            ]);
        });
    }
}
