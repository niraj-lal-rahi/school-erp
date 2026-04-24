<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expenseCategory = $this->route('expenseCategory');

        if ($expenseCategory instanceof ExpenseCategory) {
            return $this->user()?->can('update', $expenseCategory) ?? false;
        }

        return $this->user()?->can('create', ExpenseCategory::class) ?? false;
    }

    public function rules(): array
    {
        $expenseCategory = $this->route('expenseCategory');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_expense_categories', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($expenseCategory?->id),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
