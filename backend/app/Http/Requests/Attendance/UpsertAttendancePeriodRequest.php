<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAttendancePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $periodId = $this->route('attendancePeriod')?->id;

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
            'sequence' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
