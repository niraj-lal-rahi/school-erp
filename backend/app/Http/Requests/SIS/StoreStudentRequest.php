<?php

namespace App\Http\Requests\SIS;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Student::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'admission_no' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'preferred_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date'],
            'admission_date' => ['required', 'date'],
            'blood_group' => ['nullable', 'string', 'max:8'],
            'status' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'array'],
            'medical_notes' => ['nullable', 'string'],
            'guardians' => ['required', 'array', 'min:1'],
            'guardians.*.id' => ['required', 'integer', 'exists:guardians,id'],
            'guardians.*.relationship' => ['nullable', 'string', 'max:50'],
            'guardians.*.is_primary' => ['nullable', 'boolean'],
            'guardians.*.is_emergency_contact' => ['nullable', 'boolean'],
            'guardians.*.pickup_authorized' => ['nullable', 'boolean'],
            'enrollment' => ['required', 'array'],
            'enrollment.academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'enrollment.school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'enrollment.section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'enrollment.roll_number' => ['nullable', 'string', 'max:50'],
            'enrollment.status' => ['required', 'string', 'max:30'],
            'enrollment.joined_on' => ['required', 'date'],
            'admission' => ['required', 'array'],
            'admission.academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'admission.applied_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'admission.status' => ['required', 'string', 'max:30'],
            'admission.applied_on' => ['required', 'date'],
            'admission.admitted_on' => ['nullable', 'date'],
            'admission.remarks' => ['nullable', 'string'],
        ];
    }
}
