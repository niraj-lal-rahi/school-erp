<?php

namespace App\Http\Requests\SIS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('students.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string'],
            'effective_status' => ['nullable', 'string', Rule::in(['submitted', 'under_review', 'approved', 'rejected', 'waitlisted'])],
        ];
    }
}
