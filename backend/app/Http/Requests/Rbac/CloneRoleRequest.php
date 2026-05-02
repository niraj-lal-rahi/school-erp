<?php

namespace App\Http\Requests\Rbac;

class CloneRoleRequest extends RbacRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', $this->uniqueRoleName()],
            'code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/', $this->uniqueRoleCode()],
            'description' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = $this->resolveRole('id') ?: $this->resolveRole('role');

            if (! $this->canManageRole($role)) {
                $validator->errors()->add('role', 'You are not allowed to clone this role.');
            }
        });
    }
}
