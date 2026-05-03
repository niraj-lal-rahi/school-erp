<?php

namespace App\Repositories\Eloquent\Documents;

use App\Models\Documents\DocumentTag;
use App\Repositories\Contracts\Documents\DocumentTagRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentTagRepository implements DocumentTagRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return DocumentTag::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->withCount('documents')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return DocumentTag::query()->orderBy('name')->get();
    }

    public function findOrFail(int $id): DocumentTag
    {
        return DocumentTag::query()->findOrFail($id);
    }

    public function create(array $attributes): DocumentTag
    {
        $tag = DocumentTag::create($attributes);

        return $this->findOrFail($tag->id);
    }

    public function delete(DocumentTag $tag): void
    {
        $tag->delete();
    }

    public function syncDocumentTags(int $documentId, int $schoolId, array $tagIds): void
    {
        DB::table('document_tag_mappings')->where('document_id', $documentId)->delete();

        $rows = collect($tagIds)
            ->unique()
            ->map(fn (int $tagId) => [
                'school_id' => $schoolId,
                'document_id' => $documentId,
                'tag_id' => $tagId,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            DB::table('document_tag_mappings')->insert($rows);
        }
    }
}
