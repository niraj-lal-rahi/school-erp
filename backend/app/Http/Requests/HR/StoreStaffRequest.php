<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\EmploymentType;
use App\Enums\HR\StaffStatus;
use App\Enums\HR\StaffType;
use App\Models\HR\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Staff::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('hr_departments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'designation_id' => [
                'nullable',
                'integer',
                Rule::exists('hr_designations', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'employee_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('staff', 'employee_code')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('staff', 'email')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'staff_type' => ['required', 'string', Rule::in(StaffType::values())],
            'employment_type' => ['required', 'string', Rule::in(EmploymentType::values())],
            'joining_date' => ['required', 'date'],
            'leaving_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'current_status' => ['required', 'string', Rule::in(StaffStatus::values())],
            'qualification_summary' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
