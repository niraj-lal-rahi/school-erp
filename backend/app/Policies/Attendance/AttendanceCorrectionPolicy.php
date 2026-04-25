<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\AttendanceCorrection;
use App\Models\User;

class AttendanceCorrectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendanceCorrection $correction): bool
    {
        return $user->school_id === $correction->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, AttendanceCorrection $correction): bool
    {
        return $user->school_id === $correction->school_id && $user->hasPermission('attendance.manage');
    }
}
