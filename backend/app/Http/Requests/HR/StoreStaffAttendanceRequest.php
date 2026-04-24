<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\AttendanceSource;
use App\Enums\HR\AttendanceStatus;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Staff $staff */
        $staff = $this->route('staff');

        return $staff instanceof Staff
            ? ($this->user()?->can('update', $staff) ?? false)
            : ($this->user()?->can('create', Staff::class) ?? false);
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;
        $staff = $this->route('staff');

        return [
            'staff_id' => [
                Rule::requiredIf(! $staff instanceof Staff),
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'attendance_date' => [
                'required',
                'date',
            ],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i', 'after:check_in_time'],
            'attendance_status' => ['required', 'string', Rule::in(AttendanceStatus::values())],
            'source' => ['nullable', 'string', Rule::in(AttendanceSource::values())],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $schoolId = $this->user()?->school_id;
            $staff = $this->route('staff');
            $staffId = $staff instanceof Staff ? $staff->id : (int) $this->input('staff_id');
            $attendanceDate = $this->input('attendance_date');

            if (! $staffId || ! $attendanceDate || ! $schoolId) {
                return;
            }

            $exists = StaffAttendance::query()
                ->where('school_id', $schoolId)
                ->where('staff_id', $staffId)
                ->whereDate('attendance_date', $attendanceDate)
                ->exists();

            if ($exists) {
                $validator->errors()->add('attendance_date', 'Attendance has already been recorded for this staff member on the selected date.');
            }
        });
    }
}
