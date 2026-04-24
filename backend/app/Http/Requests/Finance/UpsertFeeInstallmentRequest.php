<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\FeeInstallmentStatus;
use App\Models\Finance\FeeInstallment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFeeInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $feeInstallment = $this->route('feeInstallment');

        if ($feeInstallment instanceof FeeInstallment) {
            return $this->user()?->can('update', $feeInstallment) ?? false;
        }

        return $this->user()?->can('create', FeeInstallment::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'student_fee_assignment_id' => ['required', 'integer', Rule::exists('finance_student_fee_assignments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'fee_head_id' => ['required', 'integer', Rule::exists('finance_fee_heads', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'installment_name' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(FeeInstallmentStatus::values())],
        ];
    }
}
