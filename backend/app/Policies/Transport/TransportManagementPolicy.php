<?php

namespace App\Policies\Transport;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TransportManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('transport.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->school_id === $model->school_id && $user->hasPermission('transport.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('transport.manage');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->school_id === $model->school_id && $user->hasPermission('transport.manage');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->school_id === $model->school_id && $user->hasPermission('transport.manage');
    }
}
