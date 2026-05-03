<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findForLogin(?int $schoolId, string $email): ?User
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('email', $email)
            ->where('school_id', $schoolId)
            ->first();
    }

    public function findPlatformUserForLogin(string $email): ?User
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('email', $email)
            ->whereNull('school_id')
            ->first();
    }
}
