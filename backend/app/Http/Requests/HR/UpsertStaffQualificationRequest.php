<?php

namespace App\Http\Requests\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffQualification;
use Illuminate\Foundation\Http\FormRequest;

class UpsertStaffQualificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        $qualification = $this->route('staffQualification');

        if ($staff instanceof Staff) {
            return $this->user()?->can('update', $staff) ?? false;
        }

        if ($qualification instanceof StaffQualification) {
            return $this->user()?->can('update', $qualification->staff) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'degree' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'board_or_university' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'passing_year' => ['nullable', 'integer', 'between:1950,2100'],
            'percentage_or_grade' => ['nullable', 'string', 'max:100'],
            'document_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
