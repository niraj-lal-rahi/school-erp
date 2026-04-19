<?php

namespace App\Http\Requests\SIS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('students.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'admission_no' => ['required', 'string', 'max:50'],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'joining_date' => ['nullable', 'date'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'current_status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'transferred', 'withdrawn', 'alumni', 'graduated', 'suspended'])],
            'guardian_id' => ['nullable', 'integer', 'exists:guardians,id'],
        ];
    }
}
