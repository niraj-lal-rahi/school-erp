<?php

namespace App\Services\Saas;

use App\Models\User;
use App\Repositories\Contracts\Saas\TenantAuditRepositoryInterface;

class TenantAuditService
{
    public function __construct(
        protected TenantAuditRepositoryInterface $audits,
    ) {
    }

    public function log(
        string $action,
        ?int $schoolId = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $performedBy = null,
        ?string $ipAddress = null,
    ) {
        return $this->audits->create([
            'school_id' => $schoolId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'performed_by' => $performedBy?->id,
            'ip_address' => $ipAddress,
        ]);
    }
}
