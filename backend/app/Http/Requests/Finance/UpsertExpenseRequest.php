<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\ExpenseStatus;
use App\Models\Finance\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');

        if ($expense instanceof Expense) {
            return $this->user()?->can('update', $expense) ?? false;
        }

        return $this->user()?->can('create', Expense::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'expense_category_id' => ['required', 'integer', Rule::exists('finance_expense_categories', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'attachment_path' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', Rule::in(ExpenseStatus::values())],
        ];
    }
}
