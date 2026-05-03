<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\ReminderRule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReminderRuleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function activeRules(): Collection;

    public function findOrFail(int $id): ReminderRule;

    public function create(array $attributes): ReminderRule;

    public function update(ReminderRule $reminderRule, array $attributes): ReminderRule;

    public function delete(ReminderRule $reminderRule): void;

    public function createLog(array $attributes);
}
