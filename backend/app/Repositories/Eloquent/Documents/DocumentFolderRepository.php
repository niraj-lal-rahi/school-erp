<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentFolder;
use App\Repositories\Contracts\Documents\DocumentFolderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocumentFolderRepository implements DocumentFolderRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when(array_key_exists('parent_id', $filters), fn (Builder $query) => $query->where('parent_id', $filters['parent_id']))
            ->when($filters['visibility'] ?? null, fn (Builder $query, string $value) => $query->where('visibility', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function roots(): Collection
    {
        return $this->query()->whereNull('parent_id')->orderBy('name')->get();
    }

    public function childrenOf(?int $parentId): Collection
    {
        return $this->query()->where('parent_id', $parentId)->orderBy('name')->get();
    }

    public function findOrFail(int $id): DocumentFolder
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): DocumentFolder
    {
        $folder = DocumentFolder::create($attributes);

        return $this->findOrFail($folder->id);
    }

    public function update(DocumentFolder $folder, array $attributes): DocumentFolder
    {
        $folder->update($attributes);

        return $this->findOrFail($folder->id);
    }

    public function delete(DocumentFolder $folder): void
    {
        $folder->delete();
    }

    protected function query(): Builder
    {
        return DocumentFolder::query()
            ->with(['parent', 'creator'])
            ->withCount(['children', 'documents']);
    }
}
