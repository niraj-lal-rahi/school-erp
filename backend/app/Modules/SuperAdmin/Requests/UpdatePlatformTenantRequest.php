<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformTenantRequest extends FormRequest
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
        $tenantId = (int) $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', Rule::unique('platform_tenants', 'code')->ignore($tenantId)],
            'slug' => ['sometimes', 'required', 'string', 'max:150', 'alpha_dash', Rule::unique('platform_tenants', 'slug')->ignore($tenantId)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['sometimes', 'required', Rule::in(['trial', 'active', 'suspended', 'cancelled', 'expired'])],
            'trial_ends_at' => ['nullable', 'date'],
            'activated_at' => ['nullable', 'date'],
            'suspended_at' => ['nullable', 'date'],
        ];
    }
}
