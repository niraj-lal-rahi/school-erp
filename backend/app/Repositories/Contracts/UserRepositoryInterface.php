<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findForLogin(?int $schoolId, string $email): ?User;

    public function findPlatformUserForLogin(string $email): ?User;
}
