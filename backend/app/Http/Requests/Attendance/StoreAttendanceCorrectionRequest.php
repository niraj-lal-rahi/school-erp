<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'reference_type' => ['required', 'string', Rule::in(['student', 'staff'])],
            'reference_id' => ['required', 'integer'],
            'attendance_date' => ['required', 'date'],
            'new_status_id' => ['required', 'integer', Rule::exists('attendance_status_types', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'reason' => ['required', 'string'],
        ];
    }
}
