<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\IntegrationSetting;
use App\Repositories\Contracts\Settings\IntegrationSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IntegrationSettingRepository implements IntegrationSettingRepositoryInterface
{
    public function list(array $filters = []): Collection
    {
        return $this->query()
            ->when($filters['integration_type'] ?? null, fn (Builder $query, string $value) => $query->where('integration_type', $value))
            ->when($filters['provider'] ?? null, fn (Builder $query, string $value) => $query->where('provider', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->orderBy('integration_type')
            ->get();
    }

    public function findOrFail(int $id): IntegrationSetting
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): IntegrationSetting
    {
        return IntegrationSetting::withoutGlobalScopes()->create($attributes)->fresh();
    }

    public function update(IntegrationSetting $integration, array $attributes): IntegrationSetting
    {
        $integration->update($attributes);

        return $integration->fresh();
    }

    public function delete(IntegrationSetting $integration): void
    {
        $integration->delete();
    }

    protected function query(): Builder
    {
        return IntegrationSetting::query();
    }
}
