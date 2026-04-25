<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckTimetableConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timetable_version_id' => ['required', 'integer', 'exists:timetable_versions,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'day_of_week' => ['required', 'string', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'attendance_period_id' => ['required', 'integer', 'exists:attendance_periods,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'room_id' => ['nullable', 'integer', 'exists:timetable_rooms,id'],
            'entry_type' => ['required', 'string', Rule::in(['class', 'break', 'activity', 'free'])],
            'ignore_entry_id' => ['nullable', 'integer', 'exists:timetable_entries,id'],
        ];
    }
}
