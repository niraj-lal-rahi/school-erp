<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\StaffStatus;
use App\Models\HR\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffStatusActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');

        return $staff instanceof Staff
            ? ($this->user()?->can('update', $staff) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'action_type' => ['required', 'string', 'max:100'],
            'new_status' => ['required', 'string', Rule::in(StaffStatus::values())],
            'reason' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
        ];
    }
}
