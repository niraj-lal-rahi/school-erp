<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAttendanceHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'applies_to' => ['required', 'string', Rule::in(['all', 'students', 'staff', 'class', 'section'])],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'is_recurring' => ['nullable', 'boolean'],
        ];
    }
}
