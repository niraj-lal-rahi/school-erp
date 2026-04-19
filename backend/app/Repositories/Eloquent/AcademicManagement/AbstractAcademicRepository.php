<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\AcademicCrudRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractAcademicRepository implements AcademicCrudRepositoryInterface
{
    protected array $searchable = [];
    protected array $filterable = [];
    protected array $with = [];

    abstract protected function model(): Model;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    foreach ($this->searchable as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $searchQuery->{$method}($column, 'like', "%{$search}%");
                    }
                });
            })
            ->when($this->filterable, function (Builder $query) use ($filters): void {
                foreach ($this->filterable as $column) {
                    if (array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                        $query->where($column, $filters[$column]);
                    }
                }
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $payload): Model
    {
        return $this->model()->create($payload);
    }

    public function update(Model $model, array $payload): Model
    {
        $model->update($payload);

        return $model->refresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    protected function query(): Builder
    {
        return $this->model()->newQuery()->with($this->with);
    }
}
