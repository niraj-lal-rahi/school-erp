<?php

namespace App\Http\Requests\Examination;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGradingSystemRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', $this->uniqueInTenant('grading_systems', 'code')],
            'grading_type' => ['required', 'string', Rule::in(['percentage', 'grade', 'gpa'])],
            'pass_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('grading_type') === 'percentage' && ! $this->filled('pass_percentage')) {
                    $validator->errors()->add('pass_percentage', 'Pass percentage is required for percentage-based grading systems.');
                }
            },
        ];
    }
}
