<?php

namespace App\Policies;

use App\Models\StudentCategory;
use App\Models\User;

class StudentCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('students.view');
    }

    public function update(User $user, StudentCategory $category): bool
    {
        return $user->school_id === $category->school_id && $user->hasPermission('students.update');
    }

    public function delete(User $user, StudentCategory $category): bool
    {
        return $this->update($user, $category);
    }
}
