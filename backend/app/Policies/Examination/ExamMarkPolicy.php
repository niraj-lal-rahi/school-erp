<?php

namespace App\Policies\Examination;

use App\Models\Examination\ExamMark;
use App\Models\User;

class ExamMarkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('exams.view');
    }

    public function view(User $user, ExamMark $examMark): bool
    {
        return $user->school_id === $examMark->school_id && $user->hasPermission('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('exams.manage');
    }

    public function update(User $user, ExamMark $examMark): bool
    {
        return $user->school_id === $examMark->school_id && $user->hasPermission('exams.manage');
    }

    public function delete(User $user, ExamMark $examMark): bool
    {
        return $user->school_id === $examMark->school_id && $user->hasPermission('exams.manage');
    }
}
