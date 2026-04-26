<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportRoute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportRouteRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportRoute;

    public function create(array $attributes): TransportRoute;

    public function update(TransportRoute $route, array $attributes): TransportRoute;

    public function delete(TransportRoute $route): void;
}
