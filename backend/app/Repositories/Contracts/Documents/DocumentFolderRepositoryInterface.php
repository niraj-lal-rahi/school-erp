<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentFolder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentFolderRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function roots(): Collection;

    public function childrenOf(?int $parentId): Collection;

    public function findOrFail(int $id): DocumentFolder;

    public function create(array $attributes): DocumentFolder;

    public function update(DocumentFolder $folder, array $attributes): DocumentFolder;

    public function delete(DocumentFolder $folder): void;
}
