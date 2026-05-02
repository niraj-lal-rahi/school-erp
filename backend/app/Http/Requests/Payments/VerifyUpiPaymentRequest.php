<?php

namespace App\Http\Requests\Payments;

use Illuminate\Validation\Rule;

class VerifyUpiPaymentRequest extends PaymentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id') ?: null;

        return [
            'school_id' => ['required', $this->schoolExists()],
            'transaction_id' => ['required', $this->transactionExists($schoolId)],
            'upi_vpa' => ['nullable', $this->upiVpaRule()],
            'upi_reference_no' => ['required', 'string', 'max:255'],
            'verification_status' => ['nullable', Rule::in(['pending', 'verified', 'failed', 'manual_review'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
