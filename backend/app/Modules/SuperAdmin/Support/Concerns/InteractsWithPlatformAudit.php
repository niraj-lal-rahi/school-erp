<?php

namespace App\Modules\SuperAdmin\Support\Concerns;

use App\Modules\SuperAdmin\Services\PlatformAuditService;

trait InteractsWithPlatformAudit
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function auditPlatformAction(
        string $action,
        string $module,
        string $description,
        ?int $tenantId = null,
        ?int $userId = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        app(PlatformAuditService::class)->record(
            action: $action,
            module: $module,
            description: $description,
            tenantId: $tenantId,
            userId: $userId,
            metadata: $metadata,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }
}
