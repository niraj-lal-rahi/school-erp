<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\SettingGroup;
use App\Repositories\Contracts\Settings\SettingGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SettingGroupRepository implements SettingGroupRepositoryInterface
{
    public function list(array $filters = []): Collection
    {
        return $this->query()
            ->when(array_key_exists('scope', $filters), function (Builder $query) use ($filters): void {
                if ($filters['scope'] === 'global') {
                    $query->withoutGlobalScopes()->whereNull('school_id');
                }
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): SettingGroup
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): SettingGroup
    {
        return SettingGroup::withoutGlobalScopes()->create($attributes)->fresh();
    }

    public function update(SettingGroup $group, array $attributes): SettingGroup
    {
        $group->update($attributes);

        return $group->fresh();
    }

    public function delete(SettingGroup $group): void
    {
        $group->delete();
    }

    protected function query(): Builder
    {
        return SettingGroup::query()->with('settings');
    }
}
