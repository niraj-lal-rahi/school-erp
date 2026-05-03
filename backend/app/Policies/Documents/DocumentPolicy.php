<?php

namespace App\Policies\Documents;

use App\Models\Documents\Document;
use App\Models\User;
use App\Services\Documents\DocumentPermissionService;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Document $document): bool
    {
        return $this->sameTenant($user, $document->school_id)
            && ($this->canManage($user) || app(DocumentPermissionService::class)->canView($user, $document));
    }

    public function create(User $user, array $attributes = []): bool
    {
        $schoolId = (int) ($attributes['school_id'] ?? $user->school_id);

        return $this->sameTenant($user, $schoolId) && $this->canManage($user);
    }

    public function update(User $user, Document $document): bool
    {
        return $this->sameTenant($user, $document->school_id)
            && ($this->canManage($user) || app(DocumentPermissionService::class)->canUpdate($user, $document));
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->sameTenant($user, $document->school_id)
            && ($this->canManage($user) || app(DocumentPermissionService::class)->canDelete($user, $document));
    }

    public function restore(User $user, Document $document): bool
    {
        return $this->update($user, $document);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->sameTenant($user, $document->school_id)
            && ($this->canManage($user) || app(DocumentPermissionService::class)->canDownload($user, $document));
    }

    public function verify(User $user, Document $document): bool
    {
        return $this->sameTenant($user, $document->school_id)
            && ($this->canVerify($user) || app(DocumentPermissionService::class)->canVerify($user, $document));
    }

    public function managePermissions(User $user, Document $document): bool
    {
        return $this->sameTenant($user, $document->school_id) && $this->canManage($user);
    }

    protected function canView(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('documents.view')
            || $user->hasPermission('documents.manage')
            || $user->hasPermission('documents.verify');
    }

    protected function canManage(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('documents.manage');
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
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'super_admin')
                    ->orWhere('roles.slug', 'super_admin');
            })
            ->exists();
    }

    protected function isTenantAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'tenant_admin')
                    ->orWhere('roles.slug', 'school-admin');
            })
            ->where('roles.school_id', $user->school_id)
            ->exists();
    }
}
