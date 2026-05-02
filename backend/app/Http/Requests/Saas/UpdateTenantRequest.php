<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class UpdateTenantRequest extends SaasRequest
{
    public function rules(): array
    {
        $tenantId = $this->routeModelId('id') ?? $this->routeModelId('tenant');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', $this->uniqueGlobal('schools', 'code', $tenantId)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'domain' => ['nullable', 'string', 'max:255', $this->uniqueGlobal('schools', 'domain', $tenantId)],
            'subdomain' => ['nullable', 'string', 'max:255', 'alpha_dash', $this->uniqueGlobal('schools', 'subdomain', $tenantId)],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'timezone'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['sometimes', 'required', Rule::in(['trial', 'active', 'suspended', 'cancelled', 'expired'])],
            'trial_ends_at' => ['nullable', 'date'],
            'activated_at' => ['nullable', 'date'],
            'suspended_at' => ['nullable', 'date'],
        ];
    }
}
