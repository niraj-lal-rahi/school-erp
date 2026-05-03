<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\Document;
use App\Repositories\Contracts\Documents\DocumentRepositoryInterface;
use App\Support\Pagination\PaginationDefaults;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentRepository implements DocumentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
            ->when($filters['owner_type'] ?? null, fn (Builder $query, string $value) => $query->where('owner_type', $value))
            ->when($filters['owner_id'] ?? null, fn (Builder $query, int $value) => $query->where('owner_id', $value))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int $value) => $query->where('category_id', $value))
            ->when($filters['folder_id'] ?? null, fn (Builder $query, int $value) => $query->where('folder_id', $value))
            ->when($filters['verification_status'] ?? null, fn (Builder $query, string $value) => $query->where('verification_status', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['expiry_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('expiry_date', '>=', $value))
            ->when($filters['expiry_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('expiry_date', '<=', $value))
            ->when($filters['tags'] ?? null, function (Builder $query, array $tagIds): void {
                $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->whereIn('document_tags.id', $tagIds));
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('document_no', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(PaginationDefaults::resolvePerPage($perPage));
    }

    public function findOrFail(int $id): Document
    {
        return $this->detailQuery()->findOrFail($id);
    }

    public function create(array $attributes): Document
    {
        $document = Document::create($attributes);

        return $this->findOrFail($document->id);
    }

    public function update(Document $document, array $attributes): Document
    {
        $document->update($attributes);

        return $this->findOrFail($document->id);
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }

    public function restore(Document $document): Document
    {
        $document->restore();

        return $this->findOrFail($document->id);
    }

    public function expiringBetween(string $from, string $to): Collection
    {
        return $this->query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $from)
            ->whereDate('expiry_date', '<=', $to)
            ->orderBy('expiry_date')
            ->get();
    }

    public function expiredDocuments(?string $asOf = null): Collection
    {
        return $this->query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $asOf ?? now()->toDateString())
            ->orderBy('expiry_date')
            ->get();
    }

    public function storageUsage(?int $schoolId = null): array
    {
        $query = DB::table('document_files')
            ->whereNull('deleted_at')
            ->when($schoolId !== null, fn ($builder) => $builder->where('school_id', $schoolId));

        return [
            'total_files' => (int) (clone $query)->count(),
            'total_size_bytes' => (int) (clone $query)->sum('file_size'),
        ];
    }

    protected function baseQuery(): Builder
    {
        return Document::query()->select([
            'documents.id',
            'documents.school_id',
            'documents.category_id',
            'documents.folder_id',
            'documents.owner_type',
            'documents.owner_id',
            'documents.title',
            'documents.description',
            'documents.document_no',
            'documents.issue_date',
            'documents.expiry_date',
            'documents.verification_status',
            'documents.status',
            'documents.created_by',
            'documents.created_at',
            'documents.updated_at',
            'documents.deleted_at',
        ]);
    }

    protected function listQuery(): Builder
    {
        return $this->baseQuery()->with([
            'category:id,name,code,applies_to,status',
            'folder:id,name,code,visibility,parent_id',
            'creator:id,name,email',
            'currentFile:id,document_id,version_no,file_name,original_file_name,disk,mime_type,file_size,is_current,created_at',
            'tags:id,name,code',
        ]);
    }

    protected function detailQuery(): Builder
    {
        return $this->baseQuery()->with([
            'category',
            'folder',
            'creator',
            'currentFile',
            'tags',
        ]);
    }
}
