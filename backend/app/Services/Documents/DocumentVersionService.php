<?php

namespace App\Services\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentFile;
use App\Models\User;
use App\Repositories\Contracts\Documents\DocumentFileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentVersionService
{
    public function __construct(
        protected DocumentFileRepositoryInterface $files,
        protected DocumentStorageService $storage,
    ) {
    }

    public function uploadNewVersion(Document $document, UploadedFile $file, array $attributes = [], ?User $actor = null): DocumentFile
    {
        return DB::transaction(function () use ($document, $file, $attributes, $actor): DocumentFile {
            $this->files->markAllNonCurrent($document->id);

            $versionNo = $this->files->nextVersionNumber($document->id);
            $fileAttributes = $this->storage->uploadFile($file, [
                'school_id' => $document->school_id,
                'owner_type' => $document->owner_type,
                'owner_id' => $document->owner_id,
                'document_id' => $document->id,
            ], $attributes['disk'] ?? 'local');

            $documentFile = $this->files->create(array_merge($fileAttributes, [
                'school_id' => $document->school_id,
                'document_id' => $document->id,
                'version_no' => $versionNo,
                'uploaded_by' => $actor?->id,
                'is_current' => true,
            ]));

            return $documentFile;
        });
    }

    public function history(Document $document): Collection
    {
        return $this->files->versions($document->id);
    }
}
