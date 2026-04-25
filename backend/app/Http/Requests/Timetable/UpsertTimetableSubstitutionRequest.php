<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertTimetableSubstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timetable_entry_id' => ['required', 'integer', 'exists:timetable_entries,id'],
            'original_staff_id' => ['required', 'integer', 'exists:staff,id'],
            'substitute_staff_id' => ['required', 'integer', 'different:original_staff_id', 'exists:staff,id'],
            'substitution_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['planned', 'approved', 'completed', 'cancelled'])],
        ];
    }
}
