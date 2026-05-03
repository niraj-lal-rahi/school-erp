<?php

namespace App\Services\Storage;

class StoragePathBuilder
{
    public function documentPath(array $context, string $generatedName): string
    {
        $tenantId = max(0, (int) ($context['school_id'] ?? 0));
        $ownerType = strtolower((string) ($context['owner_type'] ?? 'general'));
        $ownerId = max(0, (int) ($context['owner_id'] ?? 0));

        return match ($ownerType) {
            'student' => sprintf('tenants/%d/students/%d/documents/%s', $tenantId, $ownerId, $generatedName),
            'staff' => sprintf('tenants/%d/staff/%d/documents/%s', $tenantId, $ownerId, $generatedName),
            default => sprintf('tenants/%d/%s/%d/documents/%s', $tenantId, $ownerType, $ownerId, $generatedName),
        };
    }

    public function reportPath(int $schoolId, string $generatedName): string
    {
        return sprintf('tenants/%d/reports/%s', $schoolId, $generatedName);
    }

    public function receiptPath(int $schoolId, string $generatedName): string
    {
        return sprintf('tenants/%d/receipts/%s', $schoolId, $generatedName);
    }
}
