<?php

namespace App\Http\Requests\Payments;

use Illuminate\Validation\Rule;

class VerifyPaymentRequest extends PaymentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id') ?: null;

        return [
            'school_id' => ['required', $this->schoolExists()],
            'transaction_id' => ['required', $this->transactionExists($schoolId)],
            'provider' => ['required', Rule::in(['razorpay', 'stripe', 'upi_manual', 'offline'])],
            'gateway_order_id' => ['nullable', 'string', 'max:255'],
            'gateway_payment_id' => ['nullable', 'string', 'max:255'],
            'gateway_signature' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'initiated', 'successful', 'failed', 'cancelled', 'refunded', 'manually_verified'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
