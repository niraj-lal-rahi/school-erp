<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\PayslipPaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayslipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string'],
            'payment_status' => ['sometimes', 'string', Rule::in(PayslipPaymentStatus::values())],
        ];
    }
}
