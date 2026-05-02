<?php

namespace App\Http\Requests\Rbac;

use App\Models\Role;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class RbacRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function tenantId(): ?int
    {
        return app(TenantContext::class)->id() ?? $this->user()?->school_id;
    }

    protected function routeModelId(string $key): ?int
    {
        $value = $this->route($key);

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function existsInTenant(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(
            fn ($query) => $query->where('school_id', $this->tenantId())
        );
    }

    protected function uniqueRoleCode(?int $ignoreId = null): Unique
    {
        return Rule::unique('roles', 'code')
            ->where(fn ($query) => $query->where('school_id', $this->tenantId()))
            ->ignore($ignoreId);
    }

    protected function uniqueRoleName(?int $ignoreId = null): Unique
    {
        return Rule::unique('roles', 'name')
            ->where(fn ($query) => $query->where('school_id', $this->tenantId()))
            ->ignore($ignoreId);
    }

    protected function isSuperAdmin(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'super_admin')
                    ->orWhere('roles.slug', 'super_admin');
            })
            ->exists();
    }

    protected function resolveRole(?string $routeKey = 'id'): ?Role
    {
        $routeValue = $this->route($routeKey);

        if ($routeValue instanceof Role) {
            return Role::withoutGlobalScopes()->find($routeValue->id);
        }

        $roleId = $this->routeModelId($routeKey);

        return $roleId ? Role::withoutGlobalScopes()->find($roleId) : null;
    }

    protected function canManageRole(?Role $role): bool
    {
        if (! $role) {
            return true;
        }

        if (($role->role_type === 'system' || $role->school_id === null) && ! $this->isSuperAdmin()) {
            return false;
        }

        if ($role->school_id !== null && $role->school_id !== $this->tenantId() && ! $this->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
