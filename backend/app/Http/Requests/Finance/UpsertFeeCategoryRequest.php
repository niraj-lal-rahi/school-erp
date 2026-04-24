<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\FeeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFeeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $feeCategory = $this->route('feeCategory');

        if ($feeCategory instanceof FeeCategory) {
            return $this->user()?->can('update', $feeCategory) ?? false;
        }

        return $this->user()?->can('create', FeeCategory::class) ?? false;
    }

    public function rules(): array
    {
        $feeCategory = $this->route('feeCategory');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_fee_categories', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($feeCategory?->id),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
