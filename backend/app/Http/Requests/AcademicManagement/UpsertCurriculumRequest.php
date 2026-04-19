<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use Illuminate\Validation\Rule;

class UpsertCurriculumRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'academic_term_id' => ['nullable', 'integer', 'exists:academic_terms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'sequence' => ['required', 'integer', 'min:1'],
            'learning_outcomes' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }
}
