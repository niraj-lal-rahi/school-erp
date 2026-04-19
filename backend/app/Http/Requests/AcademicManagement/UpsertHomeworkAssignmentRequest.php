<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\AcademicYear;
use Illuminate\Validation\Rule;

class UpsertHomeworkAssignmentRequest extends AcademicManagementRequest
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
            'description' => ['required', 'string'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'total_marks' => ['nullable', 'numeric', 'min:0'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $academicYear = AcademicYear::query()->find($this->integer('academic_year_id'));
                if (! $academicYear) {
                    return;
                }

                $assignedDate = $this->date('assigned_date');
                $dueDate = $this->date('due_date');

                if ($assignedDate->lt($academicYear->start_date) || $assignedDate->gt($academicYear->end_date) || $dueDate->lt($academicYear->start_date) || $dueDate->gt($academicYear->end_date)) {
                    $validator->errors()->add('assigned_date', 'Assignment dates must fall within the selected academic year.');
                }
            },
        ];
    }
}
