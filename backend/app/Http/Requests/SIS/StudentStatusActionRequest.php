<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentStatusActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Student $student */
        $student = $this->route('student');

        return $this->user()?->can('manageLifecycle', $student) ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'enrollment_date' => ['nullable', 'date'],
            'joined_on' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'enrolled', 'promoted', 'transferred', 'withdrawn', 'completed'])],
        ];
    }
}
