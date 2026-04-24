<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\ExpenseData;
use App\Models\Finance\Expense;
use App\Repositories\Contracts\Finance\ExpenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $expenseQuery) use ($search): void {
                    $expenseQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('expense_no', 'like', "%{$search}%")
                        ->orWhere('vendor_name', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->when($filters['expense_category_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('expense_category_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['expense_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('expense_date', '>=', $value))
            ->when($filters['expense_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('expense_date', '<=', $value))
            ->latest('expense_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(ExpenseData $data): Expense
    {
        return Expense::create($data->attributes);
    }

    public function update(Expense $expense, ExpenseData $data): Expense
    {
        $expense->update($data->attributes);

        return $this->query()->findOrFail($expense->id);
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function nextExpenseNumber(int $schoolId): string
    {
        $nextId = (int) Expense::withoutGlobalScopes()->where('school_id', $schoolId)->max('id') + 1;

        return 'EXP-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    protected function query(): Builder
    {
        return Expense::query()->with(['category', 'creator', 'approver']);
    }
}
