<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class StoreTenantRequest extends SaasRequest
{
    protected function prepareForValidation(): void
    {
        $tenant = $this->input('tenant');

        if (! is_array($tenant)) {
            return;
        }

        $this->merge($tenant);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', $this->uniqueGlobal('schools', 'code')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'domain' => ['nullable', 'string', 'max:255', $this->uniqueGlobal('schools', 'domain')],
            'subdomain' => ['nullable', 'string', 'max:255', 'alpha_dash', $this->uniqueGlobal('schools', 'subdomain')],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'timezone'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', Rule::in(['trial', 'active', 'suspended', 'cancelled', 'expired'])],
            'trial_ends_at' => ['nullable', 'date'],
            'activated_at' => ['nullable', 'date'],
            'suspended_at' => ['nullable', 'date'],
        ];
    }
}
