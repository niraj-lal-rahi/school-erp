<?php

namespace App\Policies\Documents;

use App\Models\Documents\DocumentVerification;
use App\Models\User;

class DocumentVerificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canVerify($user);
    }

    public function view(User $user, DocumentVerification $verification): bool
    {
        return $this->sameTenant($user, $verification->school_id) && $this->canVerify($user);
    }

    public function verify(User $user, DocumentVerification $verification): bool
    {
        return $this->sameTenant($user, $verification->school_id) && $this->canVerify($user);
    }

    public function reject(User $user, DocumentVerification $verification): bool
    {
        return $this->verify($user, $verification);
    }

    protected function canVerify(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('documents.verify')
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
