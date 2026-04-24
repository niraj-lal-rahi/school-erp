<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\RecordStatus;
use App\Models\HR\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveType = $this->route('leaveType');

        if ($leaveType instanceof LeaveType) {
            return $this->user()?->can('update', $leaveType->school?->users()->first() ?? null) ?? ($this->user()?->hasPermission('hr.manage') ?? false);
        }

        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        $leaveType = $this->route('leaveType');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('leave_types', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($leaveType?->id),
            ],
            'annual_quota' => ['nullable', 'numeric', 'min:0'],
            'carry_forward_allowed' => ['sometimes', 'boolean'],
            'paid_leave' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', Rule::in(RecordStatus::values())],
        ];
    }
}
