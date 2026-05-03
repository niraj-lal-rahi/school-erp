<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\ReminderLog;
use App\Models\Workflows\ReminderRule;
use App\Repositories\Contracts\Workflows\ReminderRuleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReminderRuleRepository implements ReminderRuleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $reminderQuery) use ($search): void {
                    $reminderQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function activeRules(): Collection
    {
        return $this->query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();
    }

    public function findOrFail(int $id): ReminderRule
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ReminderRule
    {
        $reminderRule = ReminderRule::create($attributes);

        return $this->findOrFail($reminderRule->id);
    }

    public function update(ReminderRule $reminderRule, array $attributes): ReminderRule
    {
        $reminderRule->update($attributes);

        return $this->findOrFail($reminderRule->id);
    }

    public function delete(ReminderRule $reminderRule): void
    {
        $reminderRule->delete();
    }

    public function createLog(array $attributes): ReminderLog
    {
        return ReminderLog::create($attributes);
    }

    protected function query(): Builder
    {
        return ReminderRule::query()
            ->with(['template'])
            ->withCount(['logs']);
    }
}
