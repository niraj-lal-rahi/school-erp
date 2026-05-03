<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentFile;
use App\Repositories\Contracts\Documents\DocumentFileRepositoryInterface;
use Illuminate\Support\Collection;

class DocumentFileRepository implements DocumentFileRepositoryInterface
{
    public function findOrFail(int $id): DocumentFile
    {
        return DocumentFile::query()->with(['document', 'uploader'])->findOrFail($id);
    }

    public function currentForDocument(int $documentId): ?DocumentFile
    {
        return DocumentFile::query()->where('document_id', $documentId)->where('is_current', true)->latest('version_no')->first();
    }

    public function versions(int $documentId): Collection
    {
        return DocumentFile::query()
            ->where('document_id', $documentId)
            ->with('uploader')
            ->orderByDesc('version_no')
            ->get();
    }

    public function create(array $attributes): DocumentFile
    {
        $file = DocumentFile::create($attributes);

        return $this->findOrFail($file->id);
    }

    public function update(DocumentFile $documentFile, array $attributes): DocumentFile
    {
        $documentFile->update($attributes);

        return $this->findOrFail($documentFile->id);
    }

    public function markAllNonCurrent(int $documentId): void
    {
        DocumentFile::query()->where('document_id', $documentId)->update(['is_current' => false]);
    }

    public function nextVersionNumber(int $documentId): int
    {
        return ((int) DocumentFile::query()->where('document_id', $documentId)->max('version_no')) + 1;
    }

    public function delete(DocumentFile $documentFile): void
    {
        $documentFile->delete();
    }
}
