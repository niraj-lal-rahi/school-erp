<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportRoute;
use App\Repositories\Contracts\Transport\TransportRouteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportRouteRepository implements TransportRouteRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $routeQuery) use ($search): void {
                    $routeQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('start_location', 'like', "%{$search}%")
                        ->orWhere('end_location', 'like', "%{$search}%");
                });
            })
            ->when($filters['route_type'] ?? null, fn (Builder $query, string $value) => $query->where('route_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportRoute
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportRoute
    {
        $route = TransportRoute::create($attributes);

        return $this->findOrFail($route->id);
    }

    public function update(TransportRoute $route, array $attributes): TransportRoute
    {
        $route->update($attributes);

        return $this->findOrFail($route->id);
    }

    public function delete(TransportRoute $route): void
    {
        $route->delete();
    }

    protected function query(): Builder
    {
        return TransportRoute::query()
            ->withCount(['stops', 'routeAssignments', 'studentAllocations', 'staffAllocations']);
    }
}
