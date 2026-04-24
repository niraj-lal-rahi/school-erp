<?php

namespace App\Http\Requests\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffWorkExperience;
use Illuminate\Foundation\Http\FormRequest;

class UpsertStaffWorkExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        $experience = $this->route('staffWorkExperience');

        if ($staff instanceof Staff) {
            return $this->user()?->can('update', $staff) ?? false;
        }

        if ($experience instanceof StaffWorkExperience) {
            return $this->user()?->can('update', $experience->staff) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['sometimes', 'boolean'],
            'responsibilities' => ['nullable', 'string'],
            'experience_letter_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
