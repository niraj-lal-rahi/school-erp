<?php

namespace App\Services\Settings;

use App\Models\Settings\SettingGroup;
use App\Repositories\Contracts\Settings\SettingGroupRepositoryInterface;
use Illuminate\Support\Collection;

class SettingGroupService
{
    public function __construct(
        protected SettingGroupRepositoryInterface $groups,
    ) {
    }

    public function list(array $filters = []): Collection
    {
        return $this->groups->list($filters);
    }

    public function findOrFail(int $id): SettingGroup
    {
        return $this->groups->findOrFail($id);
    }

    public function create(array $attributes): SettingGroup
    {
        return $this->groups->create($attributes);
    }

    public function update(SettingGroup $group, array $attributes): SettingGroup
    {
        return $this->groups->update($group, $attributes);
    }

    public function delete(SettingGroup $group): void
    {
        $this->groups->delete($group);
    }
}
