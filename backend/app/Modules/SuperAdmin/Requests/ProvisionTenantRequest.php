<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProvisionTenantRequest extends FormRequest
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
            'tenant' => ['required', 'array'],
            'tenant.name' => ['required', 'string', 'max:255'],
            'tenant.code' => ['required', 'string', 'max:100', 'alpha_dash'],
            'tenant.slug' => ['nullable', 'string', 'max:150', 'alpha_dash'],
            'tenant.email' => ['nullable', 'email', 'max:255'],
            'tenant.phone' => ['nullable', 'string', 'max:30'],
            'tenant.domain' => ['nullable', 'string', 'max:255'],
            'tenant.timezone' => ['nullable', 'string', 'max:120'],
            'tenant.locale' => ['nullable', 'string', 'max:20'],
            'tenant.currency' => ['nullable', 'string', 'max:10'],
            'tenant.country' => ['nullable', 'string', 'max:10'],
            'tenant.storage_disk' => ['nullable', 'string', 'max:50'],
            'admin' => ['required', 'array'],
            'admin.first_name' => ['required', 'string', 'max:255'],
            'admin.last_name' => ['required', 'string', 'max:255'],
            'admin.email' => ['required', 'email', 'max:255'],
            'admin.phone' => ['nullable', 'string', 'max:30'],
            'admin.status' => ['nullable', 'in:active,inactive'],
            'plan_id' => ['nullable', 'integer', 'min:1'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }
}
