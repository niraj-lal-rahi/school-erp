<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\ExpenseCategoryData;
use App\Models\Finance\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseCategoryRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(ExpenseCategoryData $data): ExpenseCategory;

    public function update(ExpenseCategory $expenseCategory, ExpenseCategoryData $data): ExpenseCategory;

    public function delete(ExpenseCategory $expenseCategory): void;
}
