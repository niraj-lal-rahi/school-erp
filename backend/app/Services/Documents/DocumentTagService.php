<?php

namespace App\Services\Documents;

use App\Models\Documents\DocumentTag;
use App\Repositories\Contracts\Documents\DocumentTagRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentTagService
{
    public function __construct(
        protected DocumentTagRepositoryInterface $tags,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tags->paginate($filters, $perPage);
    }

    public function all(): Collection
    {
        return $this->tags->all();
    }

    public function findOrFail(int $id): DocumentTag
    {
        return $this->tags->findOrFail($id);
    }

    public function create(array $attributes): DocumentTag
    {
        return DB::transaction(fn (): DocumentTag => $this->tags->create($attributes));
    }

    public function delete(DocumentTag $tag): void
    {
        DB::transaction(fn () => $this->tags->delete($tag));
    }
}
