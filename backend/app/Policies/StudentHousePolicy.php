<?php

namespace App\Policies;

use App\Models\StudentHouse;
use App\Models\User;

class StudentHousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('students.view');
    }

    public function update(User $user, StudentHouse $house): bool
    {
        return $user->school_id === $house->school_id && $user->hasPermission('students.update');
    }

    public function delete(User $user, StudentHouse $house): bool
    {
        return $this->update($user, $house);
    }
}
