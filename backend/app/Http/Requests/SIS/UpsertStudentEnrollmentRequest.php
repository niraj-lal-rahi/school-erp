<?php

namespace App\Http\Requests\SIS;

use App\Models\StudentEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enrollment = $this->route('studentEnrollment');

        if ($enrollment instanceof StudentEnrollment) {
            return $this->user()?->hasPermission('students.update') ?? false;
        }

        return $this->user()?->hasPermission('students.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'enrollment_date' => ['nullable', 'date'],
            'joined_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(['pending', 'enrolled', 'promoted', 'transferred', 'withdrawn', 'completed'])],
            'is_current' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
