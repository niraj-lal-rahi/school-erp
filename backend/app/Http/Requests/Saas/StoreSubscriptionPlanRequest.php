<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class StoreSubscriptionPlanRequest extends SaasRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', $this->uniqueGlobal('subscription_plans', 'code')],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_staff' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
