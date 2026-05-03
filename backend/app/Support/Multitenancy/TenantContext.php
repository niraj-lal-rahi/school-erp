<?php

namespace App\Support\Multitenancy;

use App\Models\Platform\PlatformTenant;
use App\Models\School;

class TenantContext
{
    public function __construct(
        protected School|PlatformTenant|null $tenant = null,
    ) {
    }

    public function set(School|PlatformTenant|null $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): School|PlatformTenant|null
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function setTenant(PlatformTenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function getTenant(): ?PlatformTenant
    {
        return $this->tenant instanceof PlatformTenant ? $this->tenant : null;
    }

    public function getTenantId(): ?int
    {
        return $this->getTenant()?->id;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
