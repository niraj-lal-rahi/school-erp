<?php

namespace App\Http\Requests\HR;

use App\Models\HR\SalaryComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalaryStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'items' => ['nullable', 'array'],
            'items.*.salary_component_id' => ['required', 'integer', Rule::exists('salary_components', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.percentage' => ['nullable', 'numeric', 'min:0'],
            'items.*.component_type' => ['required', 'string', Rule::in(['earning', 'deduction'])],
        ];
    }
}
