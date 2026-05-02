<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Validation\Rule;

class StoreRoleRequest extends RbacRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', $this->uniqueRoleName()],
            'code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/', $this->uniqueRoleCode()],
            'description' => ['nullable', 'string'],
            'role_type' => ['nullable', Rule::in(['system', 'tenant'])],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $requestedType = $this->input('role_type', 'tenant');

            if ($requestedType === 'system' && ! $this->isSuperAdmin()) {
                $validator->errors()->add('role_type', 'Only a super admin can create a system role.');
            }
        });
    }
}
