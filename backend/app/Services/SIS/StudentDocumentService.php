<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentDocumentData;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Repositories\Contracts\StudentDocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentDocumentService
{
    public function __construct(
        protected StudentDocumentRepositoryInterface $documents,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->documents->paginate($filters, $perPage);
    }

    public function show(StudentDocument $document): StudentDocument
    {
        return $this->documents->findOrFail($document->id);
    }

    public function upload(Student $student, StudentDocumentData $data, int $uploadedBy): StudentDocument
    {
        return DB::transaction(function () use ($student, $data, $uploadedBy): StudentDocument {
            $disk = (string) config('filesystems.default', 'local');
            $path = Storage::disk($disk)->putFile(
                'students/'.$student->id.'/documents',
                $data->file
            );

            return $this->documents->create($student, [
                'school_id' => $student->school_id,
                'uploaded_by' => $uploadedBy,
                'document_type' => $data->documentType,
                'title' => $data->title,
                'disk' => $disk,
                'file_path' => $path,
                'file_name' => $data->file->getClientOriginalName(),
                'mime_type' => $data->file->getMimeType(),
                'file_size' => $data->file->getSize(),
                'issued_by' => $data->metadata['issued_by'] ?? null,
                'issued_date' => $data->metadata['issued_date'] ?? null,
                'expiry_date' => $data->metadata['expiry_date'] ?? null,
                'verification_status' => $data->metadata['verification_status'] ?? null,
                'remarks' => $data->metadata['remarks'] ?? null,
                'metadata' => [
                    'original_name' => $data->file->getClientOriginalName(),
                    'mime_type' => $data->file->getMimeType(),
                    'size' => $data->file->getSize(),
                    ...$data->metadata,
                ],
            ]);
        });
    }

    public function update(StudentDocument $document, array $attributes): StudentDocument
    {
        return DB::transaction(fn (): StudentDocument => $this->documents->update($document, $attributes));
    }

    public function delete(StudentDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            Storage::disk($document->disk)->delete($document->file_path);
            $this->documents->delete($document);
        });
    }

    public function allForStudent(Student $student): Collection
    {
        return $this->documents->allForStudent($student);
    }
}
