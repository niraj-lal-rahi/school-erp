<?php

namespace App\Services\Documents;

use App\Repositories\Contracts\Documents\DocumentRepositoryInterface;

class DocumentStorageUsageService
{
    public function __construct(
        protected DocumentRepositoryInterface $documents,
    ) {
    }

    public function usage(?int $schoolId = null): array
    {
        return $this->documents->storageUsage($schoolId);
    }
}
