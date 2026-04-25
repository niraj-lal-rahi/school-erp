<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\AttendanceSource;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\HR\StaffAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attendance = $this->route('staffAttendance') ?? $this->route('staffRecord');

        return $attendance instanceof StaffAttendance
            ? ($this->user()?->can('update', $attendance->staff) ?? false)
            : false;
    }

    public function rules(): array
    {
        /** @var StaffAttendance $attendance */
        $attendance = $this->route('staffAttendance') ?? $this->route('staffRecord');
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
            'attendance_status' => ['nullable', 'string', 'max:50'],
            'attendance_status_type_id' => [
                'nullable',
                'integer',
                Rule::exists('attendance_status_types', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'source' => ['nullable', 'string', Rule::in(AttendanceSource::values())],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $schoolId = $this->user()?->school_id;

            if (! $schoolId) {
                return;
            }

            if ($this->filled('attendance_status_type_id')) {
                $statusType = AttendanceStatusType::query()->find($this->integer('attendance_status_type_id'));

                if (! $statusType || $statusType->school_id !== $schoolId) {
                    $validator->errors()->add('attendance_status_type_id', 'The selected attendance status type is invalid for this tenant.');
                }
            }
        });
    }
}
