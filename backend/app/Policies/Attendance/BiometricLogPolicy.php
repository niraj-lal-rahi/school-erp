<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\BiometricLog;
use App\Models\User;

class BiometricLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, BiometricLog $biometricLog): bool
    {
        return $user->school_id === $biometricLog->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, BiometricLog $biometricLog): bool
    {
        return $user->school_id === $biometricLog->school_id && $user->hasPermission('attendance.manage');
    }
}
