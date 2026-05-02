<?php

namespace App\Http\Requests\Payments;

class InitiateUpiPaymentRequest extends InitiatePaymentRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['payment_method'] = ['required', 'in:upi'];
        $rules['upi_vpa'] = ['required', $this->upiVpaRule()];
        $rules['payee_name'] = ['nullable', 'string', 'max:255'];
        $rules['expires_at'] = ['nullable', 'date', 'after:now'];

        return $rules;
    }
}
