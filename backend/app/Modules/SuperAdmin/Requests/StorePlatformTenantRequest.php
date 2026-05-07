<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:platform_tenants,code'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', 'unique:platform_tenants,slug'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(['trial', 'active', 'suspended', 'cancelled', 'expired'])],
            'trial_ends_at' => ['nullable', 'date'],
            'activated_at' => ['nullable', 'date'],
            'suspended_at' => ['nullable', 'date'],
        ];
    }
}
