<?php

namespace App\Policies\Portal;

use App\Models\Portal\PortalNotification;
use App\Models\User;

class PortalNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('portal.view') || $user->hasPermission('portal.manage');
    }

    public function view(User $user, PortalNotification $notification): bool
    {
        return $user->school_id === $notification->school_id
            && (int) $user->id === (int) $notification->user_id
            && ($user->hasPermission('portal.view') || $user->hasPermission('portal.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('portal.manage');
    }

    public function update(User $user, PortalNotification $notification): bool
    {
        return $this->view($user, $notification);
    }

    public function delete(User $user, PortalNotification $notification): bool
    {
        return $user->school_id === $notification->school_id
            && $user->hasPermission('portal.manage');
    }

    public function markRead(User $user, PortalNotification $notification): bool
    {
        return $this->view($user, $notification);
    }
}
