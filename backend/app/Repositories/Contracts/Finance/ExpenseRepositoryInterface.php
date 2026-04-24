<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\ExpenseData;
use App\Models\Finance\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ExpenseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(ExpenseData $data): Expense;

    public function update(Expense $expense, ExpenseData $data): Expense;

    public function delete(Expense $expense): void;

    public function nextExpenseNumber(int $schoolId): string;
}
