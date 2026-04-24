<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\PaymentMethod;
use App\Enums\Finance\PaymentStatus;
use App\Models\Finance\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Payment::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'fee_invoice_id' => ['nullable', 'integer', Rule::exists('finance_fee_invoices', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
            'gateway_provider' => ['nullable', 'string', 'max:100'],
            'gateway_transaction_id' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['nullable', 'string', Rule::in(PaymentStatus::values())],
            'remarks' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.fee_invoice_id' => ['required', 'integer', Rule::exists('finance_fee_invoices', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'allocations.*.fee_invoice_item_id' => ['nullable', 'integer', Rule::exists('finance_fee_invoice_items', 'id')],
            'allocations.*.fee_installment_id' => ['nullable', 'integer', Rule::exists('finance_fee_installments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'allocations.*.allocated_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
