<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use Illuminate\Validation\Rule;

class UpdateAcademicYearStatusRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }
}
