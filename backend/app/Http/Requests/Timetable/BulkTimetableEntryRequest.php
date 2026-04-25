<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkTimetableEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.timetable_version_id' => ['required', 'integer', 'exists:timetable_versions,id'],
            'entries.*.academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'entries.*.school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'entries.*.section_id' => ['required', 'integer', 'exists:sections,id'],
            'entries.*.day_of_week' => ['required', 'string', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'entries.*.attendance_period_id' => ['required', 'integer', 'exists:attendance_periods,id'],
            'entries.*.subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'entries.*.staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'entries.*.room_id' => ['nullable', 'integer', 'exists:timetable_rooms,id'],
            'entries.*.entry_type' => ['required', 'string', Rule::in(['class', 'break', 'activity', 'free'])],
            'entries.*.notes' => ['nullable', 'string'],
            'entries.*.status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
