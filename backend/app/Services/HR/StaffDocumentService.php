<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffDocumentData;
use App\Models\HR\Staff;
use App\Models\HR\StaffDocument;
use App\Repositories\Contracts\HR\StaffDocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffDocumentService
{
    public function __construct(
        protected StaffDocumentRepositoryInterface $documents,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->documents->paginate($filters, $perPage);
    }

    public function show(StaffDocument $document): StaffDocument
    {
        return $this->documents->findOrFail($document->id);
    }

    public function upload(Staff $staff, StaffDocumentData $data, int $uploadedBy): StaffDocument
    {
        return DB::transaction(function () use ($staff, $data, $uploadedBy): StaffDocument {
            $disk = (string) config('filesystems.default', 'local');
            $path = Storage::disk($disk)->putFile('staff/'.$staff->id.'/documents', $data->file);

            return $this->documents->create($staff, [
                'school_id' => $staff->school_id,
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
            ]);
        });
    }

    public function update(StaffDocument $document, array $attributes): StaffDocument
    {
        return DB::transaction(fn (): StaffDocument => $this->documents->update($document, $attributes));
    }

    public function delete(StaffDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            Storage::disk($document->disk)->delete($document->file_path);
            $this->documents->delete($document);
        });
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->documents->allForStaff($staff);
    }
}
