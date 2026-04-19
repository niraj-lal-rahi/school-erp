<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Student::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = app(TenantContext::class)->id() ?? $this->user()?->school_id;

        return [
            'admission_no' => ['required', 'string', 'max:50', Rule::unique('students', 'admission_no')->where('school_id', $schoolId)],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'preferred_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', Rule::unique('students', 'email')->where('school_id', $schoolId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'aadhaar_no' => ['nullable', 'string', 'max:32'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'admission_date' => ['required', 'date'],
            'joining_date' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:8'],
            'current_status' => ['required_without:status', 'string', Rule::in(['applicant', 'active', 'inactive', 'transferred', 'withdrawn', 'alumni', 'graduated', 'suspended'])],
            'status' => ['nullable', 'string', Rule::in(['applicant', 'active', 'inactive', 'transferred', 'withdrawn', 'alumni', 'graduated', 'suspended'])],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'array'],
            'category_id' => ['nullable', 'integer', 'exists:student_categories,id'],
            'house_id' => ['nullable', 'integer', 'exists:student_houses,id'],
            'medical_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'guardians' => ['nullable', 'array'],
            'guardians.*.id' => ['required', 'integer', 'exists:guardians,id'],
            'guardians.*.relationship' => ['nullable', 'string', 'max:50'],
            'guardians.*.relationship_label' => ['nullable', 'string', 'max:100'],
            'guardians.*.is_primary' => ['nullable', 'boolean'],
            'guardians.*.is_emergency_contact' => ['nullable', 'boolean'],
            'guardians.*.pickup_authorized' => ['nullable', 'boolean'],
            'guardians.*.financial_responsibility_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'guardians.*.notes' => ['nullable', 'string'],
            'enrollment' => ['nullable', 'array'],
            'enrollment.academic_year_id' => ['required_with:enrollment', 'integer', 'exists:academic_years,id'],
            'enrollment.school_class_id' => ['required_with:enrollment', 'integer', 'exists:school_classes,id'],
            'enrollment.section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'enrollment.roll_number' => ['nullable', 'string', 'max:50'],
            'enrollment.status' => ['nullable', 'string', Rule::in(['pending', 'enrolled', 'promoted', 'transferred', 'withdrawn', 'completed', 'active', 'inactive'])],
            'enrollment.joined_on' => ['nullable', 'date'],
            'enrollment.enrollment_date' => ['nullable', 'date'],
            'enrollment.is_current' => ['nullable', 'boolean'],
            'admission' => ['nullable', 'array'],
            'admission.academic_year_id' => ['required_with:admission', 'integer', 'exists:academic_years,id'],
            'admission.applied_class_id' => ['required_with:admission', 'integer', 'exists:school_classes,id'],
            'admission.status' => ['nullable', 'string', Rule::in(['applied', 'reviewing', 'accepted', 'rejected', 'admitted'])],
            'admission.applied_on' => ['nullable', 'date'],
            'admission.admitted_on' => ['nullable', 'date'],
            'admission.remarks' => ['nullable', 'string'],
        ];
    }
}
