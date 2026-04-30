<?php

namespace App\Repositories\Contracts\Portal;

use App\Models\Portal\PortalActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PortalActivityLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $attributes): PortalActivityLog;
}
