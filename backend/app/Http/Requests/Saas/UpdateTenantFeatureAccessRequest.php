<?php

namespace App\Http\Requests\Saas;

class UpdateTenantFeatureAccessRequest extends SaasRequest
{
    public function rules(): array
    {
        return [
            'features' => ['required', 'array', 'min:1'],
            'features.*.feature_code' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9._-]+$/i'],
            'features.*.module' => ['required', 'string', 'max:100'],
            'features.*.is_enabled' => ['nullable', 'boolean'],
            'features.*.limit_value' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
