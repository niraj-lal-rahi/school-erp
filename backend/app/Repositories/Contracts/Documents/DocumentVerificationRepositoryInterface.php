<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentVerification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentVerificationRepositoryInterface
{
    public function paginatePending(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function listByDocument(int $documentId): Collection;

    public function latestForDocument(int $documentId): ?DocumentVerification;

    public function findOrFail(int $id): DocumentVerification;

    public function create(array $attributes): DocumentVerification;

    public function update(DocumentVerification $verification, array $attributes): DocumentVerification;
}
