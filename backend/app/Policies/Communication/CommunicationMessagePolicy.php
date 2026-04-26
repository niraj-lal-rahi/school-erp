<?php

namespace App\Policies\Communication;

use App\Models\Communication\CommunicationMessage;
use App\Models\User;

class CommunicationMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, CommunicationMessage $message): bool
    {
        return $user->school_id === $message->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, CommunicationMessage $message): bool
    {
        return $user->school_id === $message->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, CommunicationMessage $message): bool
    {
        return $user->school_id === $message->school_id && $user->hasPermission('communication.manage');
    }

    public function sendMessage(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }
}
