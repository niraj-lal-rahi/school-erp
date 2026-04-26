<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportTrip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportTripRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportTrip;

    public function create(array $attributes): TransportTrip;

    public function update(TransportTrip $trip, array $attributes): TransportTrip;

    public function delete(TransportTrip $trip): void;

    public function currentForAssignmentOnDate(int $assignmentId, string $tripDate, string $tripType, ?int $ignoreId = null): ?TransportTrip;
}
