<?php

namespace App\Policies\Communication;

use App\Models\Communication\CommunicationConversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, CommunicationConversation $conversation): bool
    {
        return $user->school_id === $conversation->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, CommunicationConversation $conversation): bool
    {
        return $user->school_id === $conversation->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, CommunicationConversation $conversation): bool
    {
        return $user->school_id === $conversation->school_id && $user->hasPermission('communication.manage');
    }

    public function manageParticipants(User $user, CommunicationConversation $conversation): bool
    {
        return $user->school_id === $conversation->school_id && $user->hasPermission('communication.manage');
    }
}
