<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkMarkStudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'records.*.attendance_status_type_id' => ['required', 'integer', Rule::exists('attendance_status_types', 'id')],
            'records.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'records.*.check_out_time' => ['nullable', 'date_format:H:i', 'after:records.*.check_in_time'],
            'records.*.remarks' => ['nullable', 'string'],
        ];
    }
}
