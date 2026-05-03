<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentCategory;
use App\Repositories\Contracts\Documents\DocumentCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocumentCategoryRepository implements DocumentCategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['applies_to'] ?? null, fn (Builder $query, string $value) => $query->where('applies_to', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function activeList(): Collection
    {
        return $this->query()->where('status', 'active')->orderBy('name')->get();
    }

    public function findOrFail(int $id): DocumentCategory
    {
        return $this->query()->findOrFail($id);
    }

    public function findByCode(string $code, int $schoolId): ?DocumentCategory
    {
        return DocumentCategory::withoutGlobalScopes()->where('school_id', $schoolId)->where('code', $code)->first();
    }

    public function create(array $attributes): DocumentCategory
    {
        $category = DocumentCategory::create($attributes);

        return $this->findOrFail($category->id);
    }

    public function update(DocumentCategory $category, array $attributes): DocumentCategory
    {
        $category->update($attributes);

        return $this->findOrFail($category->id);
    }

    public function delete(DocumentCategory $category): void
    {
        $category->delete();
    }

    protected function query(): Builder
    {
        return DocumentCategory::query()->withCount('documents');
    }
}
