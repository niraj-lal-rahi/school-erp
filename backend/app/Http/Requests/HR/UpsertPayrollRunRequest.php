<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\PayrollRunStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        $run = $this->route('payrollRun');
        $schoolId = $this->user()?->school_id;

        return [
            'payroll_month' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('payroll_runs')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->where('payroll_year', $this->input('payroll_year')))
                    ->ignore($run?->id),
            ],
            'payroll_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'status' => ['sometimes', 'string', Rule::in(PayrollRunStatus::values())],
        ];
    }
}
