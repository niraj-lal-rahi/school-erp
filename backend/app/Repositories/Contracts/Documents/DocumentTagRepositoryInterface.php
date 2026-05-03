<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentTagRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function all(): Collection;

    public function findOrFail(int $id): DocumentTag;

    public function create(array $attributes): DocumentTag;

    public function delete(DocumentTag $tag): void;

    public function syncDocumentTags(int $documentId, int $schoolId, array $tagIds): void;
}
