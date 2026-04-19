<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStudentGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Student|null $student */
        $student = $this->route('student');

        return $student ? ($this->user()?->can('update', $student) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'guardian_id' => ['required', 'integer', 'exists:guardians,id'],
            'relationship' => ['nullable', 'string', 'max:50'],
            'relationship_label' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
            'is_emergency_contact' => ['nullable', 'boolean'],
            'pickup_authorized' => ['nullable', 'boolean'],
            'financial_responsibility_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
