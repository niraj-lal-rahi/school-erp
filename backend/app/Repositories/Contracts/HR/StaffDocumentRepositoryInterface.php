<?php

namespace App\Repositories\Contracts\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StaffDocumentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StaffDocument;

    public function create(Staff $staff, array $attributes): StaffDocument;

    public function update(StaffDocument $document, array $attributes): StaffDocument;

    public function delete(StaffDocument $document): void;

    public function allForStaff(Staff $staff): Collection;
}
