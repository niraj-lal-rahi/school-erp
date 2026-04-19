<?php

namespace App\Repositories\Contracts;

use App\Models\School;

interface SchoolRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?School;
}
