<?php

namespace App\Policies\AcademicManagement;

use App\Models\User;

class AcademicManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->school_id !== null;
    }

    public function view(User $user, object $model): bool
    {
        return $user->school_id === ($model->school_id ?? null);
    }

    public function create(User $user): bool
    {
        return $user->school_id !== null;
    }

    public function update(User $user, object $model): bool
    {
        return $user->school_id === ($model->school_id ?? null);
    }

    public function delete(User $user, object $model): bool
    {
        return $user->school_id === ($model->school_id ?? null);
    }
}
