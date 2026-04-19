<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentData;
use App\DataTransferObjects\SIS\StudentDocumentData;
use App\Events\SIS\StudentCreated;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function create(StudentData $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $student = $this->students->create($data);
            event(new StudentCreated($student));

            return $student;
        });
    }

    public function update(Student $student, StudentData $data): Student
    {
        return DB::transaction(fn (): Student => $this->students->update($student, $data));
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student): void {
            $this->students->delete($student);
        });
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
