<?php

namespace App\Repositories\Contracts\AcademicManagement;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface AcademicCrudRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Model;

    public function create(array $payload): Model;

    public function update(Model $model, array $payload): Model;

    public function delete(Model $model): void;
}
