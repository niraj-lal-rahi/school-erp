<?php

namespace App\Policies\Examination;

use App\Models\Examination\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('exams.view');
    }

    public function view(User $user, Exam $exam): bool
    {
        return $user->school_id === $exam->school_id && $user->hasPermission('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('exams.manage');
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->school_id === $exam->school_id && $user->hasPermission('exams.manage');
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->school_id === $exam->school_id && $user->hasPermission('exams.manage');
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->school_id === $exam->school_id && $user->hasPermission('exams.manage');
    }
}
