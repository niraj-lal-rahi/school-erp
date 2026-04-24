<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\Payment;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $payment instanceof Payment
            ? ($this->user()?->can('update', $payment) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'gateway_provider' => ['nullable', 'string', 'max:100'],
            'gateway_transaction_id' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
