<?php

namespace App\Support\Multitenancy;

use App\Models\School;

class TenantContext
{
    public function __construct(
        protected ?School $school = null,
    ) {
    }

    public function set(?School $school): void
    {
        $this->school = $school;
    }

    public function get(): ?School
    {
        return $this->school;
    }

    public function id(): ?int
    {
        return $this->school?->id;
    }

    public function hasTenant(): bool
    {
        return $this->school !== null;
    }
}
