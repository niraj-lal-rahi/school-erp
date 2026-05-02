<?php

namespace App\Http\Requests\Saas;

use Illuminate\Validation\Rule;

class StoreTenantDomainRequest extends SaasRequest
{
    public function rules(): array
    {
        return [
            'domain' => ['required', 'string', 'max:255', $this->uniqueGlobal('tenant_domains', 'domain')],
            'domain_type' => ['required', Rule::in(['primary', 'custom', 'subdomain'])],
            'is_verified' => ['nullable', 'boolean'],
            'verified_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
