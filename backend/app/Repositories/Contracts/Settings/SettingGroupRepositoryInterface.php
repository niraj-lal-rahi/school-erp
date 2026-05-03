<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\SettingGroup;
use Illuminate\Support\Collection;

interface SettingGroupRepositoryInterface
{
    public function list(array $filters = []): Collection;

    public function findOrFail(int $id): SettingGroup;

    public function create(array $attributes): SettingGroup;

    public function update(SettingGroup $group, array $attributes): SettingGroup;

    public function delete(SettingGroup $group): void;
}
