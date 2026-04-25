<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\StudentAttendanceSession;
use App\Models\User;

class StudentAttendanceSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, StudentAttendanceSession $session): bool
    {
        return $user->school_id === $session->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, StudentAttendanceSession $session): bool
    {
        return $user->school_id === $session->school_id && $user->hasPermission('attendance.manage');
    }

    public function delete(User $user, StudentAttendanceSession $session): bool
    {
        return $user->school_id === $session->school_id && $user->hasPermission('attendance.manage');
    }
}
