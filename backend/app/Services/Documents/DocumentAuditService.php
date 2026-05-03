<?php

namespace App\Services\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentAuditLog;
use App\Models\User;
use App\Repositories\Contracts\Documents\DocumentAuditRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DocumentAuditService
{
    public function __construct(
        protected DocumentAuditRepositoryInterface $audits,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->audits->paginate($filters, $perPage);
    }

    public function listByDocument(int $documentId): Collection
    {
        return $this->audits->listByDocument($documentId);
    }

    public function log(Document|int|null $document, string $action, ?User $actor = null, array $metadata = [], ?Request $request = null): DocumentAuditLog
    {
        $documentId = $document instanceof Document ? $document->id : $document;
        $schoolId = $document instanceof Document ? $document->school_id : ($actor?->school_id ?? app(\App\Support\TenantContext::class)->id());

        return $this->audits->create([
            'school_id' => $schoolId,
            'document_id' => $documentId,
            'action' => $action,
            'performed_by' => $actor?->id,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
