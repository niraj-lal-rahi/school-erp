<?php

namespace App\Http\Requests\SIS;

use App\Models\Admission;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admission = $this->route('studentAdmission');

        if ($admission instanceof Admission) {
            return $this->user()?->hasPermission('students.update') ?? false;
        }

        return $this->user()?->hasPermission('students.create') ?? false;
    }

    public function rules(): array
    {
        $schoolId = app(TenantContext::class)->id() ?? $this->user()?->school_id;
        $admission = $this->route('studentAdmission');

        return [
            'application_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('admissions', 'application_no')->where('school_id', $schoolId)->ignore($admission?->id),
            ],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'applied_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'application_status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'under_review', 'approved', 'rejected', 'waitlisted', 'converted'])],
        ];
    }
}
