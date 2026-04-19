<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\AcademicYear;
use Illuminate\Validation\Rule;

class UpsertAcademicYearRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var AcademicYear|null $academicYear */
        $academicYear = $this->route('academicYear');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_years', 'code')
                    ->where('school_id', $this->tenantId())
                    ->ignore($academicYear?->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }
}
