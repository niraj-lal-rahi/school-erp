<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\AttendanceSource;
use App\Enums\HR\AttendanceStatus;
use App\Models\HR\StaffAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attendance = $this->route('staffAttendance');

        return $attendance instanceof StaffAttendance
            ? ($this->user()?->can('update', $attendance->staff) ?? false)
            : false;
    }

    public function rules(): array
    {
        /** @var StaffAttendance $attendance */
        $attendance = $this->route('staffAttendance');
        $schoolId = $this->user()?->school_id;

        return [
            'attendance_date' => [
                'sometimes',
                'date',
                Rule::unique('staff_attendance', 'attendance_date')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->where('staff_id', $attendance->staff_id))
                    ->ignore($attendance->id),
            ],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i', 'after:check_in_time'],
            'attendance_status' => ['sometimes', 'string', Rule::in(AttendanceStatus::values())],
            'source' => ['nullable', 'string', Rule::in(AttendanceSource::values())],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
