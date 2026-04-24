<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\ExpenseData;
use App\Enums\Finance\ExpenseStatus;
use App\Models\Finance\Expense;
use App\Repositories\Contracts\Finance\ExpenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        protected ExpenseRepositoryInterface $expenses,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->expenses->paginate($filters, $perPage);
    }

    public function create(ExpenseData $data): Expense
    {
        return DB::transaction(fn (): Expense => $this->expenses->create(ExpenseData::fromArray([
            ...$data->attributes,
            'expense_no' => $data->attributes['expense_no'] ?? $this->expenses->nextExpenseNumber((int) $data->attributes['school_id']),
            'status' => $data->attributes['status'] ?? ExpenseStatus::Draft->value,
        ])));
    }

    public function update(Expense $expense, ExpenseData $data): Expense
    {
        if (! in_array($expense->status, [ExpenseStatus::Draft->value, ExpenseStatus::Rejected->value], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only draft or rejected expenses can be updated.',
            ]);
        }

        return DB::transaction(fn (): Expense => $this->expenses->update($expense, $data));
    }

    public function delete(Expense $expense): void
    {
        if ($expense->status === ExpenseStatus::Paid->value) {
            throw ValidationException::withMessages([
                'status' => 'Paid expenses cannot be deleted.',
            ]);
        }

        DB::transaction(fn (): bool => tap($expense)->delete());
    }

    public function approve(Expense $expense, int $approvedBy): Expense
    {
        if ($expense->status !== ExpenseStatus::Draft->value) {
            throw ValidationException::withMessages([
                'status' => 'Only draft expenses can be approved.',
            ]);
        }

        return DB::transaction(fn (): Expense => $this->expenses->update($expense, ExpenseData::fromArray([
            ...$expense->only([
                'school_id',
                'expense_category_id',
                'expense_no',
                'title',
                'description',
                'amount',
                'expense_date',
                'payment_method',
                'vendor_name',
                'reference_no',
                'attachment_path',
                'created_by',
            ]),
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'status' => ExpenseStatus::Approved->value,
        ])));
    }

    public function markPaid(Expense $expense): Expense
    {
        if ($expense->status !== ExpenseStatus::Approved->value) {
            throw ValidationException::withMessages([
                'status' => 'Only approved expenses can be marked as paid.',
            ]);
        }

        return DB::transaction(fn (): Expense => $this->expenses->update($expense, ExpenseData::fromArray([
            ...$expense->only([
                'school_id',
                'expense_category_id',
                'expense_no',
                'title',
                'description',
                'amount',
                'expense_date',
                'payment_method',
                'vendor_name',
                'reference_no',
                'attachment_path',
                'created_by',
                'approved_by',
                'approved_at',
            ]),
            'paid_at' => now(),
            'status' => ExpenseStatus::Paid->value,
        ])));
    }
}
