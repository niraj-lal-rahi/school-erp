<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportRouteStop;
use App\Repositories\Contracts\Transport\TransportRouteStopRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TransportRouteStopRepository implements TransportRouteStopRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['route_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('route_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $stopQuery) use ($search): void {
                    $stopQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('route_id')
            ->orderBy('stop_order')
            ->paginate($perPage);
    }

    public function forRoute(int $routeId): Collection
    {
        return $this->query()
            ->where('route_id', $routeId)
            ->orderBy('stop_order')
            ->get();
    }

    public function findOrFail(int $id): TransportRouteStop
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportRouteStop
    {
        $routeStop = TransportRouteStop::create($attributes);

        return $this->findOrFail($routeStop->id);
    }

    public function update(TransportRouteStop $routeStop, array $attributes): TransportRouteStop
    {
        $routeStop->update($attributes);

        return $this->findOrFail($routeStop->id);
    }

    public function delete(TransportRouteStop $routeStop): void
    {
        $routeStop->delete();
    }

    protected function query(): Builder
    {
        return TransportRouteStop::query()->with('route');
    }
}
