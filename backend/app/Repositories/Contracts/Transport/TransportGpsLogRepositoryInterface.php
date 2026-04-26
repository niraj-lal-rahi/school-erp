<?php

namespace App\Repositories\Contracts\Transport;

use App\Models\Transport\TransportGpsLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransportGpsLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): TransportGpsLog;

    public function create(array $attributes): TransportGpsLog;

    public function latestVehicleLocation(int $vehicleId): ?TransportGpsLog;
}
