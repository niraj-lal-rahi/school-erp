<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\ExpenseCategoryData;
use App\Models\Finance\ExpenseCategory;
use App\Repositories\Contracts\Finance\ExpenseCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryService
{
    public function __construct(
        protected ExpenseCategoryRepositoryInterface $expenseCategories,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->expenseCategories->all($filters);
    }

    public function create(ExpenseCategoryData $data): ExpenseCategory
    {
        return DB::transaction(fn (): ExpenseCategory => $this->expenseCategories->create($data));
    }

    public function update(ExpenseCategory $expenseCategory, ExpenseCategoryData $data): ExpenseCategory
    {
        return DB::transaction(fn (): ExpenseCategory => $this->expenseCategories->update($expenseCategory, $data));
    }

    public function delete(ExpenseCategory $expenseCategory): void
    {
        DB::transaction(fn (): bool => tap($expenseCategory)->delete());
    }
}
