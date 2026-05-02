<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class StorePlanFeatureRequest extends SaasRequest
{
    public function rules(): array
    {
        return [
            'subscription_plan_id' => ['required', $this->existsGlobal('subscription_plans')],
            'feature_code' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9._-]+$/i'],
            'feature_name' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', 'max:100'],
            'is_enabled' => ['nullable', 'boolean'],
            'limit_value' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
