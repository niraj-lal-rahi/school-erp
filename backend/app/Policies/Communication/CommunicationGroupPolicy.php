<?php

namespace App\Policies\Communication;

use App\Models\Communication\CommunicationGroup;
use App\Models\User;

class CommunicationGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, CommunicationGroup $group): bool
    {
        return $user->school_id === $group->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, CommunicationGroup $group): bool
    {
        return $user->school_id === $group->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, CommunicationGroup $group): bool
    {
        return $user->school_id === $group->school_id && $user->hasPermission('communication.manage');
    }

    public function manageParticipants(User $user, CommunicationGroup $group): bool
    {
        return $user->school_id === $group->school_id && $user->hasPermission('communication.manage');
    }
}
