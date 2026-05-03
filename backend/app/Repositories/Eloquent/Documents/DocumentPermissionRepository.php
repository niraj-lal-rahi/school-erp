<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentPermission;
use App\Repositories\Contracts\Documents\DocumentPermissionRepositoryInterface;
use Illuminate\Support\Collection;

class DocumentPermissionRepository implements DocumentPermissionRepositoryInterface
{
    public function listByDocument(int $documentId): Collection
    {
        return DocumentPermission::query()->with(['user', 'role'])->where('document_id', $documentId)->orderBy('id')->get();
    }

    public function findOrFail(int $id): DocumentPermission
    {
        return DocumentPermission::query()->with(['document', 'user', 'role'])->findOrFail($id);
    }

    public function create(array $attributes): DocumentPermission
    {
        $permission = DocumentPermission::create($attributes);

        return $this->findOrFail($permission->id);
    }

    public function update(DocumentPermission $permission, array $attributes): DocumentPermission
    {
        $permission->update($attributes);

        return $this->findOrFail($permission->id);
    }

    public function delete(DocumentPermission $permission): void
    {
        $permission->delete();
    }

    public function deleteByDocument(int $documentId): void
    {
        DocumentPermission::query()->where('document_id', $documentId)->delete();
    }
}
