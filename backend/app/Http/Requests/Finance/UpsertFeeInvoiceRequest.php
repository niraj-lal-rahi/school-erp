<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\FeeInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFeeInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('feeInvoice');

        if ($invoice instanceof FeeInvoice) {
            return $this->user()?->can('update', $invoice) ?? false;
        }

        return $this->user()?->can('create', FeeInvoice::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string'],
            'installment_ids' => ['required', 'array', 'min:1'],
            'installment_ids.*' => ['integer', Rule::exists('finance_fee_installments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
        ];
    }
}
