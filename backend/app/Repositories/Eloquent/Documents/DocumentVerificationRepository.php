<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentVerification;
use App\Repositories\Contracts\Documents\DocumentVerificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocumentVerificationRepository implements DocumentVerificationRepositoryInterface
{
    public function paginatePending(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return DocumentVerification::query()
            ->with(['document.currentFile', 'verifier'])
            ->where('status', 'pending')
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function listByDocument(int $documentId): Collection
    {
        return DocumentVerification::query()->with('verifier')->where('document_id', $documentId)->latest('id')->get();
    }

    public function latestForDocument(int $documentId): ?DocumentVerification
    {
        return DocumentVerification::query()->where('document_id', $documentId)->latest('id')->first();
    }

    public function findOrFail(int $id): DocumentVerification
    {
        return DocumentVerification::query()->with(['document', 'verifier'])->findOrFail($id);
    }

    public function create(array $attributes): DocumentVerification
    {
        $verification = DocumentVerification::create($attributes);

        return $this->findOrFail($verification->id);
    }

    public function update(DocumentVerification $verification, array $attributes): DocumentVerification
    {
        $verification->update($attributes);

        return $this->findOrFail($verification->id);
    }
}
