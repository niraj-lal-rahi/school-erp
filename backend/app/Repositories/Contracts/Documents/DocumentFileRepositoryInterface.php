<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentFile;
use Illuminate\Support\Collection;

interface DocumentFileRepositoryInterface
{
    public function findOrFail(int $id): DocumentFile;

    public function currentForDocument(int $documentId): ?DocumentFile;

    public function versions(int $documentId): Collection;

    public function create(array $attributes): DocumentFile;

    public function update(DocumentFile $documentFile, array $attributes): DocumentFile;

    public function markAllNonCurrent(int $documentId): void;

    public function nextVersionNumber(int $documentId): int;

    public function delete(DocumentFile $documentFile): void;
}
