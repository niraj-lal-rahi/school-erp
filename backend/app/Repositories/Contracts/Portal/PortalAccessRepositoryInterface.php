<?php

namespace App\Repositories\Contracts\Portal;

use App\Models\Portal\PortalProfileAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PortalAccessRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getAccessibleStudents(int $userId): Collection;

    public function getActiveAccessesByUser(int $userId): Collection;

    public function findOrFail(int $id): PortalProfileAccess;

    public function create(array $attributes): PortalProfileAccess;

    public function update(PortalProfileAccess $portalProfileAccess, array $attributes): PortalProfileAccess;

    public function delete(PortalProfileAccess $portalProfileAccess): void;
}
