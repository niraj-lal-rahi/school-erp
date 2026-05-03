<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentCategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function activeList(): Collection;

    public function findOrFail(int $id): DocumentCategory;

    public function findByCode(string $code, int $schoolId): ?DocumentCategory;

    public function create(array $attributes): DocumentCategory;

    public function update(DocumentCategory $category, array $attributes): DocumentCategory;

    public function delete(DocumentCategory $category): void;
}
