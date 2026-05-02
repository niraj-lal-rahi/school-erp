<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class SubscribeTenantRequest extends SaasRequest
{
    public function rules(): array
    {
        return [
            'subscription_plan_id' => ['required', $this->planExists()],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'trial_ends_at' => ['nullable', 'date', 'after_or_equal:start_date'],
            'auto_renew' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['trial', 'active', 'past_due', 'suspended', 'cancelled', 'expired'])],
        ];
    }
}
