<?php

namespace App\Services\Documents;

use App\Models\Documents\DocumentCategory;
use App\Repositories\Contracts\Documents\DocumentCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentCategoryService
{
    public function __construct(
        protected DocumentCategoryRepositoryInterface $categories,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categories->paginate($filters, $perPage);
    }

    public function activeList(): Collection
    {
        return $this->categories->activeList();
    }

    public function findOrFail(int $id): DocumentCategory
    {
        return $this->categories->findOrFail($id);
    }

    public function create(array $attributes): DocumentCategory
    {
        $this->assertUniqueCode($attributes['code'], (int) $attributes['school_id']);

        return DB::transaction(fn (): DocumentCategory => $this->categories->create($attributes));
    }

    public function update(DocumentCategory $category, array $attributes): DocumentCategory
    {
        $code = $attributes['code'] ?? $category->code;
        $this->assertUniqueCode($code, (int) $category->school_id, $category->id);

        return DB::transaction(fn (): DocumentCategory => $this->categories->update($category, $attributes));
    }

    public function deactivate(DocumentCategory $category): DocumentCategory
    {
        return DB::transaction(fn (): DocumentCategory => $this->categories->update($category, ['status' => 'inactive']));
    }

    public function delete(DocumentCategory $category): void
    {
        DB::transaction(fn () => $this->categories->delete($category));
    }

    protected function assertUniqueCode(string $code, int $schoolId, ?int $ignoreId = null): void
    {
        $existing = $this->categories->findByCode($code, $schoolId);

        if ($existing && $existing->id !== $ignoreId) {
            throw ValidationException::withMessages([
                'code' => 'A document category with this code already exists for the tenant.',
            ]);
        }
    }
}
