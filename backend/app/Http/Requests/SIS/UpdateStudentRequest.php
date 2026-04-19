<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends StoreStudentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('student')) ?? false;
    }

    public function rules(): array
    {
        /** @var Student $student */
        $student = $this->route('student');
        $schoolId = app(TenantContext::class)->id() ?? $this->user()?->school_id;

        $rules = parent::rules();
        $rules['admission_no'] = ['required', 'string', 'max:50', Rule::unique('students', 'admission_no')->where('school_id', $schoolId)->ignore($student->id)];
        $rules['email'] = ['nullable', 'email', Rule::unique('students', 'email')->where('school_id', $schoolId)->ignore($student->id)];

        return $rules;
    }
}
