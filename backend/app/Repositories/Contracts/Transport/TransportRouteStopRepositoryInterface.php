<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportRouteStop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TransportRouteStopRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function forRoute(int $routeId): Collection;

    public function findOrFail(int $id): TransportRouteStop;

    public function create(array $attributes): TransportRouteStop;

    public function update(TransportRouteStop $routeStop, array $attributes): TransportRouteStop;

    public function delete(TransportRouteStop $routeStop): void;
}
