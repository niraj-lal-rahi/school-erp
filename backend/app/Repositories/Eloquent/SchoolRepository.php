<?php

namespace App\Repositories\Eloquent;

use App\Models\School;
use App\Repositories\Contracts\SchoolRepositoryInterface;

class SchoolRepository implements SchoolRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?School
    {
        return School::query()
            ->where('code', $identifier)
            ->orWhere('uuid', $identifier)
            ->orWhere('slug', $identifier)
            ->orWhere('domain', $identifier)
            ->first();
    }
}
