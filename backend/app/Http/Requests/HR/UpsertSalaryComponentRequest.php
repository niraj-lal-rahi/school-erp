<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\RecordStatus;
use App\Enums\HR\SalaryCalculationType;
use App\Enums\HR\SalaryComponentType;
use App\Models\HR\SalaryComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        $component = $this->route('salaryComponent');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('salary_components', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($component?->id),
            ],
            'component_type' => ['required', 'string', Rule::in(SalaryComponentType::values())],
            'calculation_type' => ['required', 'string', Rule::in(SalaryCalculationType::values())],
            'default_value' => ['nullable', 'numeric', 'min:0'],
            'taxable' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', Rule::in(RecordStatus::values())],
        ];
    }
}
