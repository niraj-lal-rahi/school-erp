<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\LessonPlanStatus;
use App\Models\AcademicYear;
use Illuminate\Validation\Rule;

class UpsertLessonPlanRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'academic_term_id' => ['nullable', 'integer', 'exists:academic_terms,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'staff_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'objectives' => ['required', 'string'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
            'planned_date' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'materials_needed' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(LessonPlanStatus::class)],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $academicYear = AcademicYear::query()->find($this->integer('academic_year_id'));
                if ($academicYear && ($this->date('planned_date')->lt($academicYear->start_date) || $this->date('planned_date')->gt($academicYear->end_date))) {
                    $validator->errors()->add('planned_date', 'Planned date must fall within the selected academic year.');
                }
            },
        ];
    }
}
