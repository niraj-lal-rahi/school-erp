<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\AttendanceSummary;
use App\Models\User;

class AttendanceSummaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendanceSummary $summary): bool
    {
        return $user->school_id === $summary->school_id && $user->hasPermission('attendance.view');
    }
}
