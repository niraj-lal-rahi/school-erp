<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\AcademicCrudRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class AbstractAcademicCrudService
{
    public function __construct(
        protected AcademicCrudRepositoryInterface $repository,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function show(int $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $payload): Model
    {
        return DB::transaction(fn (): Model => $this->repository->create($payload));
    }

    public function update(Model $model, array $payload): Model
    {
        return DB::transaction(fn (): Model => $this->repository->update($model, $payload));
    }

    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model): void {
            $this->repository->delete($model);
        });
    }
}
