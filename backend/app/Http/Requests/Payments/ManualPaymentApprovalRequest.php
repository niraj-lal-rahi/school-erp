<?php

namespace App\Http\Requests\Payments;

use Illuminate\Validation\Rule;

class ManualPaymentApprovalRequest extends PaymentRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['successful', 'failed', 'manually_verified', 'cancelled'])],
            'verification_status' => ['required', Rule::in(['verified', 'failed', 'manual_review'])],
            'remarks' => ['nullable', 'string'],
            'upi_reference_no' => ['required_if:status,manually_verified', 'nullable', 'string', 'max:255'],
            'verified_by' => ['nullable', $this->userExists()],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
