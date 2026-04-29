<?php

namespace App\Http\Requests\Examination;

use Illuminate\Validation\Rule;

class StoreExamTypeRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', $this->uniqueInTenant('exam_types', 'code')],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
