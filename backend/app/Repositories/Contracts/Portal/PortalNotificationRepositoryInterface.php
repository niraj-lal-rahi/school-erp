<?php

namespace App\Repositories\Contracts\Portal;

use App\Models\Portal\PortalNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PortalNotificationRepositoryInterface
{
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function unreadCount(int $userId): int;

    public function findOrFail(int $id): PortalNotification;

    public function create(array $attributes): PortalNotification;

    public function update(PortalNotification $portalNotification, array $attributes): PortalNotification;

    public function delete(PortalNotification $portalNotification): void;
}
