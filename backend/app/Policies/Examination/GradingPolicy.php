<?php

namespace App\Policies\Examination;

use App\Models\Examination\GradingSystem;
use App\Models\User;

class GradingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('exams.view');
    }

    public function view(User $user, GradingSystem $gradingSystem): bool
    {
        return $user->school_id === $gradingSystem->school_id && $user->hasPermission('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('exams.manage');
    }

    public function update(User $user, GradingSystem $gradingSystem): bool
    {
        return $user->school_id === $gradingSystem->school_id && $user->hasPermission('exams.manage');
    }

    public function delete(User $user, GradingSystem $gradingSystem): bool
    {
        return $user->school_id === $gradingSystem->school_id && $user->hasPermission('exams.manage');
    }
}
