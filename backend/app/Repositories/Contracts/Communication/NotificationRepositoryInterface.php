<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): NotificationLog;

    public function create(array $attributes): NotificationLog;

    public function update(NotificationLog $notificationLog, array $attributes): NotificationLog;

    public function delete(NotificationLog $notificationLog): void;
}
