<?php

namespace App\Http\Requests\Payments;

use Illuminate\Validation\Rule;

class InitiatePaymentRequest extends PaymentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id') ?: null;

        return [
            'school_id' => ['required', $this->schoolExists()],
            'payable_type' => ['required', Rule::in(['school_fee', 'saas_subscription', 'other'])],
            'payable_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', $this->studentExists($schoolId)],
            'tenant_subscription_id' => ['nullable', $this->existsGlobal('tenant_subscriptions')],
            'gateway_id' => ['nullable', $this->paymentGatewayExists($schoolId)],
            'provider' => ['required', Rule::in(['razorpay', 'stripe', 'upi_manual', 'offline'])],
            'payment_method' => ['required', Rule::in(['upi', 'card', 'netbanking', 'wallet', 'cash', 'bank_transfer', 'cheque', 'other'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'upi_vpa' => ['nullable', $this->upiVpaRule()],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
