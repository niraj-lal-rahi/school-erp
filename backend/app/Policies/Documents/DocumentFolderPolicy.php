<?php

namespace App\Policies\Documents;

use App\Models\Documents\DocumentFolder;
use App\Models\User;

class DocumentFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, DocumentFolder $folder): bool
    {
        return $this->sameTenant($user, $folder->school_id) && $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, DocumentFolder $folder): bool
    {
        return $this->sameTenant($user, $folder->school_id) && $this->canManage($user);
    }

    public function delete(User $user, DocumentFolder $folder): bool
    {
        return $this->update($user, $folder);
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
