<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\LeaveApplicationStatus;
use App\Models\HR\StaffLeaveApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffLeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'staff_id' => ['sometimes', 'integer', Rule::exists('staff', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'leave_type_id' => ['sometimes', 'integer', Rule::exists('leave_types', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'reason' => ['sometimes', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in([LeaveApplicationStatus::Draft->value, LeaveApplicationStatus::Submitted->value])],
        ];
    }
}
