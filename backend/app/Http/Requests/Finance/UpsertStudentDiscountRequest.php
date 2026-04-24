<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\StudentDiscountStatus;
use App\Models\Finance\StudentDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $studentDiscount = $this->route('studentDiscount');

        if ($studentDiscount instanceof StudentDiscount) {
            return $this->user()?->can('update', $studentDiscount) ?? false;
        }

        return $this->user()?->can('create', StudentDiscount::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'discount_type_id' => ['required', 'integer', Rule::exists('finance_discount_types', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'fee_head_id' => ['nullable', 'integer', Rule::exists('finance_fee_heads', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(array_column(StudentDiscountStatus::cases(), 'value'))],
        ];
    }
}
