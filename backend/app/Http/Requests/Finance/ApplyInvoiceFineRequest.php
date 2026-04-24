<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\FeeInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyInvoiceFineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('feeInvoice');

        return $invoice instanceof FeeInvoice
            ? ($this->user()?->can('update', $invoice) ?? false)
            : false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'fine_rule_id' => ['required', 'integer', Rule::exists('finance_fine_rules', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
        ];
    }
}
