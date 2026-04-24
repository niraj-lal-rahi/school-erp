<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\FeeAssignmentStatus;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignFeeStructureToStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student');

        return $student instanceof Student
            ? ($this->user()?->can('update', $student) ?? false)
            : false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'fee_structure_id' => ['required', 'integer', Rule::exists('finance_fee_structures', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'assigned_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(FeeAssignmentStatus::values())],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
