<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\ExpenseCategoryData;
use App\Models\Finance\ExpenseCategory;
use App\Repositories\Contracts\Finance\ExpenseCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return ExpenseCategory::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($categoryQuery) use ($search): void {
                    $categoryQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->withCount('expenses')
            ->orderBy('name')
            ->get();
    }

    public function create(ExpenseCategoryData $data): ExpenseCategory
    {
        return ExpenseCategory::create($data->attributes);
    }

    public function update(ExpenseCategory $expenseCategory, ExpenseCategoryData $data): ExpenseCategory
    {
        $expenseCategory->update($data->attributes);

        return $expenseCategory->refresh()->loadCount('expenses');
    }

    public function delete(ExpenseCategory $expenseCategory): void
    {
        $expenseCategory->delete();
    }
}
