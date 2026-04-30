<?php

namespace App\Policies\Portal;

use App\Models\Portal\PortalActivityLog;
use App\Models\Portal\PortalSession;
use App\Models\Portal\PortalUserProfile;
use App\Models\User;

class PortalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('portal.view') || $user->hasPermission('portal.manage');
    }

    public function view(User $user, PortalUserProfile|PortalSession|PortalActivityLog|null $portalResource = null): bool
    {
        if ($portalResource && $user->school_id !== $portalResource->school_id) {
            return false;
        }

        return $user->hasPermission('portal.view') || $user->hasPermission('portal.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('portal.manage');
    }

    public function update(User $user, PortalUserProfile|PortalSession|PortalActivityLog|null $portalResource = null): bool
    {
        if ($portalResource && $user->school_id !== $portalResource->school_id) {
            return false;
        }

        return $user->hasPermission('portal.manage');
    }

    public function delete(User $user, PortalUserProfile|PortalSession|PortalActivityLog|null $portalResource = null): bool
    {
        if ($portalResource && $user->school_id !== $portalResource->school_id) {
            return false;
        }

        return $user->hasPermission('portal.manage');
    }

    public function switchContext(User $user): bool
    {
        return $user->hasPermission('portal.view') || $user->hasPermission('portal.manage');
    }

    public function impersonate(User $user): bool
    {
        return $user->hasPermission('portal.impersonate');
    }
}
