<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\IntegrationSetting;
use Illuminate\Support\Collection;

interface IntegrationSettingRepositoryInterface
{
    public function list(array $filters = []): Collection;

    public function findOrFail(int $id): IntegrationSetting;

    public function create(array $attributes): IntegrationSetting;

    public function update(IntegrationSetting $integration, array $attributes): IntegrationSetting;

    public function delete(IntegrationSetting $integration): void;
}
