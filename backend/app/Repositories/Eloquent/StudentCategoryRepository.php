<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\StudentCategoryData;
use App\Models\StudentCategory;
use App\Repositories\Contracts\StudentCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentCategoryRepository implements StudentCategoryRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StudentCategory::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($categoryQuery) use ($search): void {
                    $categoryQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }

    public function create(StudentCategoryData $data): StudentCategory
    {
        return StudentCategory::create($data->attributes);
    }

    public function update(StudentCategory $category, StudentCategoryData $data): StudentCategory
    {
        $category->update($data->attributes);

        return $category->refresh()->loadCount('students');
    }

    public function delete(StudentCategory $category): void
    {
        $category->delete();
    }
}
