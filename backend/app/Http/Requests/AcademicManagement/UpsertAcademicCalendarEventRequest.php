<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Enums\AcademicManagement\AudienceType;
use Illuminate\Validation\Rule;

class UpsertAcademicCalendarEventRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', 'string', 'max:100'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'is_holiday' => ['nullable', 'boolean'],
            'audience_type' => ['required', Rule::enum(AudienceType::class)],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }
}
