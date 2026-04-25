<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAttendanceStatusTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statusTypeId = $this->route('attendanceStatusType')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('attendance_status_types', 'code')
                    ->where(fn ($query) => $query->where('school_id', $this->user()->school_id))
                    ->ignore($statusTypeId),
            ],
            'is_present' => ['required', 'boolean'],
            'counts_for_attendance' => ['required', 'boolean'],
            'color_code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
