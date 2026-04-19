<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Validation\Rule;

class UpsertClassSubjectAssignmentRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var ClassSubjectAssignment|null $assignment */
        $assignment = $this->route('classSubject');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'is_optional' => ['nullable', 'boolean'],
            'weekly_periods' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
            'unique_guard' => [
                Rule::unique('class_subject_assignments', 'subject_id')
                    ->where('school_id', $this->tenantId())
                    ->where('academic_year_id', $this->integer('academic_year_id'))
                    ->where('school_class_id', $this->integer('school_class_id'))
                    ->where('section_id', $this->input('section_id'))
                    ->ignore($assignment?->id),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'unique_guard' => $this->input('subject_id'),
        ]);
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $schoolClass = SchoolClass::query()->find($this->integer('school_class_id'));
                $sectionId = $this->integer('section_id');
                if ($sectionId) {
                    $section = Section::query()->find($sectionId);
                    if (! $section || $section->school_class_id !== $schoolClass?->id) {
                        $validator->errors()->add('section_id', 'Section must belong to the selected class.');
                    }
                }
            },
        ];
    }
}
