<?php

namespace App\Repositories\Contracts\Documents;

use App\Models\Documents\DocumentBulkUpload;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DocumentBulkUploadRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): DocumentBulkUpload;

    public function create(array $attributes): DocumentBulkUpload;

    public function update(DocumentBulkUpload $bulkUpload, array $attributes): DocumentBulkUpload;
}
