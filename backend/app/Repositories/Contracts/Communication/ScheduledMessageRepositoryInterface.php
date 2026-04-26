<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\ScheduledMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ScheduledMessageRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ScheduledMessage;

    public function create(array $attributes): ScheduledMessage;

    public function update(ScheduledMessage $scheduledMessage, array $attributes): ScheduledMessage;

    public function delete(ScheduledMessage $scheduledMessage): void;
}
