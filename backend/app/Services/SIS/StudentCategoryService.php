<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentCategoryData;
use App\Models\StudentCategory;
use App\Repositories\Contracts\StudentCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentCategoryService
{
    public function __construct(
        protected StudentCategoryRepositoryInterface $categories,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->categories->all($filters);
    }

    public function create(StudentCategoryData $data): StudentCategory
    {
        return DB::transaction(fn (): StudentCategory => $this->categories->create($data));
    }

    public function update(StudentCategory $category, StudentCategoryData $data): StudentCategory
    {
        return DB::transaction(fn (): StudentCategory => $this->categories->update($category, $data));
    }

    public function delete(StudentCategory $category): void
    {
        DB::transaction(fn (): bool => $category->delete());
    }
}
