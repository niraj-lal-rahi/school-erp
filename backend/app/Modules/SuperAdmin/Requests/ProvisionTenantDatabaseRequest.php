<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProvisionTenantDatabaseRequest extends FormRequest
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
            'admin' => ['required', 'array'],
            'admin.first_name' => ['required', 'string', 'max:255'],
            'admin.last_name' => ['required', 'string', 'max:255'],
            'admin.email' => ['required', 'email', 'max:255'],
            'admin.phone' => ['nullable', 'string', 'max:30'],
            'admin.status' => ['nullable', 'in:active,inactive'],
            'plan_id' => ['nullable', 'integer', 'min:1'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'tenant_profile' => ['nullable', 'array'],
            'tenant_profile.domain' => ['nullable', 'string', 'max:255'],
            'tenant_profile.timezone' => ['nullable', 'string', 'max:120'],
            'tenant_profile.locale' => ['nullable', 'string', 'max:20'],
            'tenant_profile.currency' => ['nullable', 'string', 'max:10'],
            'tenant_profile.country' => ['nullable', 'string', 'max:10'],
            'tenant_profile.storage_disk' => ['nullable', 'string', 'max:50'],
        ];
    }
}
