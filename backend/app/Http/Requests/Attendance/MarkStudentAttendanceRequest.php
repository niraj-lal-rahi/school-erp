<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkStudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_session_id' => ['required', 'integer', Rule::exists('attendance_student_sessions', 'id')],
            'attendance_status_type_id' => ['required', 'integer', Rule::exists('attendance_status_types', 'id')],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i', 'after:check_in_time'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
