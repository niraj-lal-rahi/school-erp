<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentAuditRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function listByDocument(int $documentId): Collection;

    public function create(array $attributes): DocumentAuditLog;
}
