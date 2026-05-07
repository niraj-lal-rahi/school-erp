<?php

namespace App\Modules\Tenant\Services;

use App\Modules\SuperAdmin\Models\PlatformTenant;

class TenantContext
{
    protected ?PlatformTenant $tenant = null;

    public function setTenant(PlatformTenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function getTenant(): ?PlatformTenant
    {
        return $this->tenant;
    }

    public function currentTenant(): ?PlatformTenant
    {
        return $this->tenant;
    }

    public function getTenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
