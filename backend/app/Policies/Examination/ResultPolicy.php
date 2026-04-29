<?php

namespace App\Policies\Examination;

use App\Models\Examination\StudentResult;
use App\Models\User;

class ResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('exams.view');
    }

    public function view(User $user, StudentResult $studentResult): bool
    {
        return $user->school_id === $studentResult->school_id && $user->hasPermission('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('exams.manage');
    }

    public function update(User $user, StudentResult $studentResult): bool
    {
        return $user->school_id === $studentResult->school_id && $user->hasPermission('exams.manage');
    }

    public function delete(User $user, StudentResult $studentResult): bool
    {
        return $user->school_id === $studentResult->school_id && $user->hasPermission('exams.manage');
    }

    public function publish(User $user, StudentResult $studentResult): bool
    {
        return $user->school_id === $studentResult->school_id && $user->hasPermission('exams.manage');
    }
}
