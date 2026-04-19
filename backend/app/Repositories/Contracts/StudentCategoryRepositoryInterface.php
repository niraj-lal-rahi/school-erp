<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\StudentCategoryData;
use App\Models\StudentCategory;
use Illuminate\Database\Eloquent\Collection;

interface StudentCategoryRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(StudentCategoryData $data): StudentCategory;

    public function update(StudentCategory $category, StudentCategoryData $data): StudentCategory;

    public function delete(StudentCategory $category): void;
}
