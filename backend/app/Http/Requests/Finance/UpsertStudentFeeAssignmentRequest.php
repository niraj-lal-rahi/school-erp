<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\FeeAssignmentStatus;
use App\Models\Finance\StudentFeeAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentFeeAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('studentFeeAssignment');

        if ($assignment instanceof StudentFeeAssignment) {
            return $this->user()?->can('update', $assignment) ?? false;
        }

        return $this->user()?->can('create', StudentFeeAssignment::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'fee_structure_id' => ['required', 'integer', Rule::exists('finance_fee_structures', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'assigned_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(FeeAssignmentStatus::values())],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
