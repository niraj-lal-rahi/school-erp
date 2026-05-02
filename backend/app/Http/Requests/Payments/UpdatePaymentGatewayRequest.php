<?php

namespace App\Http\Requests\Payments;

use Illuminate\Validation\Rule;

class UpdatePaymentGatewayRequest extends PaymentRequest
{
    public function rules(): array
    {
        $id = $this->routeModelId('id') ?? $this->routeModelId('gateway');
        $schoolId = $this->integer('school_id') ?: null;

        return [
            'school_id' => ['nullable', $this->schoolExists()],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('payment_gateways', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($id),
            ],
            'provider' => ['required', Rule::in(['razorpay', 'stripe', 'upi_manual', 'offline'])],
            'mode' => ['required', Rule::in(['test', 'live'])],
            'config' => ['nullable', 'array'],
            'supports_upi' => ['nullable', 'boolean'],
            'supports_card' => ['nullable', 'boolean'],
            'supports_netbanking' => ['nullable', 'boolean'],
            'supports_wallet' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'credentials' => ['nullable', 'array'],
            'credentials.*.key_name' => ['required_with:credentials', 'string', 'max:100'],
            'credentials.*.key_value' => ['nullable', 'string'],
            'credentials.*.is_encrypted' => ['nullable', 'boolean'],
        ];
    }
}
