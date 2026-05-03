<?php

namespace App\Services\Documents;

use App\Models\Documents\DocumentFolder;
use App\Repositories\Contracts\Documents\DocumentFolderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentFolderService
{
    public function __construct(
        protected DocumentFolderRepositoryInterface $folders,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->folders->paginate($filters, $perPage);
    }

    public function roots(): Collection
    {
        return $this->folders->roots();
    }

    public function findOrFail(int $id): DocumentFolder
    {
        return $this->folders->findOrFail($id);
    }

    public function create(array $attributes): DocumentFolder
    {
        if (! empty($attributes['parent_id'])) {
            $this->folders->findOrFail((int) $attributes['parent_id']);
        }

        return DB::transaction(fn (): DocumentFolder => $this->folders->create($attributes));
    }

    public function update(DocumentFolder $folder, array $attributes): DocumentFolder
    {
        if (array_key_exists('parent_id', $attributes)) {
            $this->assertValidParent($folder, $attributes['parent_id']);
        }

        return DB::transaction(fn (): DocumentFolder => $this->folders->update($folder, $attributes));
    }

    public function delete(DocumentFolder $folder): void
    {
        DB::transaction(function () use ($folder): void {
            if ($folder->children()->exists()) {
                throw ValidationException::withMessages([
                    'folder' => 'Delete or reassign child folders before deleting this folder.',
                ]);
            }

            $this->folders->delete($folder);
        });
    }

    protected function assertValidParent(DocumentFolder $folder, mixed $parentId): void
    {
        if ($parentId === null || $parentId === '') {
            return;
        }

        $parent = $this->folders->findOrFail((int) $parentId);

        if ($parent->id === $folder->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A folder cannot be its own parent.',
            ]);
        }

        $cursor = $parent;

        while ($cursor !== null) {
            if ($cursor->id === $folder->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Circular folder nesting is not allowed.',
                ]);
            }

            $cursor = $cursor->parent;
        }
    }
}
