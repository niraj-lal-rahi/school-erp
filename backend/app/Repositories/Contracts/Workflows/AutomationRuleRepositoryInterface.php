<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\AutomationRule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AutomationRuleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function allActiveByTrigger(string $triggerType, ?string $triggerEvent = null): Collection;

    public function dueScheduledRules(): Collection;

    public function findOrFail(int $id): AutomationRule;

    public function create(array $attributes): AutomationRule;

    public function update(AutomationRule $automationRule, array $attributes): AutomationRule;

    public function delete(AutomationRule $automationRule): void;
}
