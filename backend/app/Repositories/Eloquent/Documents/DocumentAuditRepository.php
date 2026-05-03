<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentAuditLog;
use App\Repositories\Contracts\Documents\DocumentAuditRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocumentAuditRepository implements DocumentAuditRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return DocumentAuditLog::query()
            ->with(['document', 'actor'])
            ->when($filters['document_id'] ?? null, fn (Builder $query, int $value) => $query->where('document_id', $value))
            ->when($filters['action'] ?? null, fn (Builder $query, string $value) => $query->where('action', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function listByDocument(int $documentId): Collection
    {
        return DocumentAuditLog::query()->with('actor')->where('document_id', $documentId)->latest('id')->get();
    }

    public function create(array $attributes): DocumentAuditLog
    {
        return DocumentAuditLog::create($attributes);
    }
}
