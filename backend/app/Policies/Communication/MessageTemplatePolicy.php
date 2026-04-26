<?php

namespace App\Policies\Communication;

use App\Models\Communication\MessageTemplate;
use App\Models\User;

class MessageTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, MessageTemplate $template): bool
    {
        return $user->school_id === $template->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, MessageTemplate $template): bool
    {
        return $user->school_id === $template->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, MessageTemplate $template): bool
    {
        return $user->school_id === $template->school_id && $user->hasPermission('communication.manage');
    }
}
