<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\AcademicCrudRepositoryInterface;
use App\Services\Cache\CacheInvalidationService;
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
        return DB::transaction(function () use ($payload): Model {
            $model = $this->repository->create($payload);
            $this->clearAcademicCache((int) ($payload['school_id'] ?? $model->school_id ?? 0));

            return $model;
        });
    }

    public function update(Model $model, array $payload): Model
    {
        return DB::transaction(function () use ($model, $payload): Model {
            $updated = $this->repository->update($model, $payload);
            $this->clearAcademicCache((int) ($updated->school_id ?? $payload['school_id'] ?? 0));

            return $updated;
        });
    }

    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model): void {
            $schoolId = (int) ($model->school_id ?? 0);
            $this->repository->delete($model);
            $this->clearAcademicCache($schoolId);
        });
    }

    protected function clearAcademicCache(?int $schoolId): void
    {
        if ($schoolId === null || $schoolId <= 0) {
            return;
        }

        app(CacheInvalidationService::class)->academicStructure($schoolId);
    }
}
