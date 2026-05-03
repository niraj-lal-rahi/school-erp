<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\AutomationRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AutomationRunRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): AutomationRun;

    public function create(array $attributes): AutomationRun;

    public function update(AutomationRun $automationRun, array $attributes): AutomationRun;

    public function createActionLog(array $attributes);
}
