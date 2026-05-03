<?php

namespace App\Policies\Documents;

use App\Models\Documents\DocumentCategory;
use App\Models\User;

class DocumentCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, DocumentCategory $category): bool
    {
        return $this->sameTenant($user, $category->school_id) && $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, DocumentCategory $category): bool
    {
        return $this->sameTenant($user, $category->school_id) && $this->canManage($user);
    }

    public function delete(User $user, DocumentCategory $category): bool
    {
        return $this->update($user, $category);
    }

    protected function canView(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('documents.view')
            || $user->hasPermission('documents.manage');
    }

    protected function canManage(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('documents.manage');
    }

    protected function sameTenant(User $user, int $schoolId): bool
    {
        return (int) $user->school_id === (int) $schoolId;
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->roles()->withoutGlobalScopes()->where(fn ($query) => $query->where('roles.code', 'super_admin')->orWhere('roles.slug', 'super_admin'))->exists();
    }

    protected function isTenantAdmin(User $user): bool
    {
        return $user->roles()->withoutGlobalScopes()->where(fn ($query) => $query->where('roles.code', 'tenant_admin')->orWhere('roles.slug', 'school-admin'))->where('roles.school_id', $user->school_id)->exists();
    }
}
