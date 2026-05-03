<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentPermission;
use Illuminate\Support\Collection;

interface DocumentPermissionRepositoryInterface
{
    public function listByDocument(int $documentId): Collection;

    public function findOrFail(int $id): DocumentPermission;

    public function create(array $attributes): DocumentPermission;

    public function update(DocumentPermission $permission, array $attributes): DocumentPermission;

    public function delete(DocumentPermission $permission): void;

    public function deleteByDocument(int $documentId): void;
}
