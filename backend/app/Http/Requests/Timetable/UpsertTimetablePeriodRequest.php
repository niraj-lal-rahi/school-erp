<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertTimetablePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $periodId = $this->route('period')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('attendance_periods', 'code')
                    ->where(fn ($query) => $query->where('school_id', $this->user()->school_id))
                    ->ignore($periodId),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('attendance_periods', 'sequence')
                    ->where(fn ($query) => $query->where('school_id', $this->user()->school_id))
                    ->ignore($periodId),
            ],
            'is_break' => ['nullable', 'boolean'],
            'break_type' => ['nullable', 'string', Rule::in(['short_break', 'lunch', 'assembly', 'activity'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
