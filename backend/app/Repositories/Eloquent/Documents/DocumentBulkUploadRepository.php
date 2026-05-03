<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentBulkUpload;
use App\Repositories\Contracts\Documents\DocumentBulkUploadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DocumentBulkUploadRepository implements DocumentBulkUploadRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return DocumentBulkUpload::query()
            ->with('uploader')
            ->when($filters['upload_type'] ?? null, fn (Builder $query, string $value) => $query->where('upload_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): DocumentBulkUpload
    {
        return DocumentBulkUpload::query()->with('uploader')->findOrFail($id);
    }

    public function create(array $attributes): DocumentBulkUpload
    {
        $bulkUpload = DocumentBulkUpload::create($attributes);

        return $this->findOrFail($bulkUpload->id);
    }

    public function update(DocumentBulkUpload $bulkUpload, array $attributes): DocumentBulkUpload
    {
        $bulkUpload->update($attributes);

        return $this->findOrFail($bulkUpload->id);
    }
}
