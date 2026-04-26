<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportRouteVehicleAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportRouteVehicleAssignmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportRouteVehicleAssignment;

    public function create(array $attributes): TransportRouteVehicleAssignment;

    public function update(TransportRouteVehicleAssignment $assignment, array $attributes): TransportRouteVehicleAssignment;

    public function delete(TransportRouteVehicleAssignment $assignment): void;

    public function activeForRouteOnDate(int $routeId, string $date, ?int $ignoreId = null): ?TransportRouteVehicleAssignment;
}
