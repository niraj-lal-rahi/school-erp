<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class UpdateSubscriptionPlanRequest extends SaasRequest
{
    public function rules(): array
    {
        $planId = $this->routeModelId('id') ?? $this->routeModelId('plan');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', $this->uniqueGlobal('subscription_plans', 'code', $planId)],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_staff' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
        ];
    }
}
