<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Document;

    public function create(array $attributes): Document;

    public function update(Document $document, array $attributes): Document;

    public function delete(Document $document): void;

    public function restore(Document $document): Document;

    public function expiringBetween(string $from, string $to): Collection;

    public function expiredDocuments(?string $asOf = null): Collection;

    public function storageUsage(?int $schoolId = null): array;
}
