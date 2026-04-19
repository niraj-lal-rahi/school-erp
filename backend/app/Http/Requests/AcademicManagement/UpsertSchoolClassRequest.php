<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\SchoolClass;
use Illuminate\Validation\Rule;

class UpsertSchoolClassRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var SchoolClass|null $schoolClass */
        $schoolClass = $this->route('schoolClass');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('school_classes', 'code')
                    ->where('school_id', $this->tenantId())
                    ->where('academic_year_id', $this->integer('academic_year_id'))
                    ->ignore($schoolClass?->id),
            ],
            'grade_level' => ['required', 'integer', 'min:0', 'max:20'],
            'level_order' => ['required', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }
}
