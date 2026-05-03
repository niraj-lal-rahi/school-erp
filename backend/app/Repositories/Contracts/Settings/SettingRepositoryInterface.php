<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\Setting;
use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    public function list(array $filters = []): Collection;

    public function findOrFail(int $id): Setting;

    public function findByKey(string $key, ?int $schoolId = null, ?string $scope = null): ?Setting;

    public function create(array $attributes): Setting;

    public function update(Setting $setting, array $attributes): Setting;

    public function delete(Setting $setting): void;

    public function allPublic(?int $schoolId = null): Collection;
}
