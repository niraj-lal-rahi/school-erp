<?php

namespace App\Services\Rbac;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\Rbac\RoleAuditLogRepositoryInterface;

class RoleAuditService
{
    public function __construct(
        protected RoleAuditLogRepositoryInterface $auditLogs,
    ) {
    }

    public function log(
        string $action,
        ?Role $role = null,
        ?User $user = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $performedBy = null,
        ?string $ipAddress = null,
    ): void {
        $schoolId = $role?->school_id
            ?? $user?->school_id
            ?? $performedBy?->school_id;

        $this->auditLogs->create([
            'school_id' => $schoolId,
            'role_id' => $role?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'performed_by' => $performedBy?->id,
            'ip_address' => $ipAddress,
        ]);
    }
}
