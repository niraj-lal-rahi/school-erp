<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\Setting;
use App\Repositories\Contracts\Settings\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SettingRepository implements SettingRepositoryInterface
{
    public function list(array $filters = []): Collection
    {
        return $this->query()
            ->when($filters['group_id'] ?? null, fn (Builder $query, int $value) => $query->where('group_id', $value))
            ->when($filters['scope'] ?? null, fn (Builder $query, string $value) => $query->where('scope', $value))
            ->when(isset($filters['is_public']), fn (Builder $query) => $query->where('is_public', (bool) $filters['is_public']))
            ->when(isset($filters['is_sensitive']), fn (Builder $query) => $query->where('is_sensitive', (bool) $filters['is_sensitive']))
            ->orderBy('key')
            ->get();
    }

    public function findOrFail(int $id): Setting
    {
        return $this->query()->findOrFail($id);
    }

    public function findByKey(string $key, ?int $schoolId = null, ?string $scope = null): ?Setting
    {
        return Setting::withoutGlobalScopes()
            ->where('key', $key)
            ->when($scope !== null, fn (Builder $query) => $query->where('scope', $scope))
            ->when($schoolId !== null, function (Builder $query) use ($schoolId): void {
                $query->where(function (Builder $inner) use ($schoolId): void {
                    $inner->where(function (Builder $tenant) use ($schoolId): void {
                        $tenant->where('scope', 'tenant')->where('school_id', $schoolId);
                    })->orWhere(function (Builder $global): void {
                        $global->where('scope', 'global')->whereNull('school_id');
                    });
                })->orderByRaw("case when school_id = {$schoolId} then 0 else 1 end");
            })
            ->orderByDesc('id')
            ->first();
    }

    public function create(array $attributes): Setting
    {
        return Setting::withoutGlobalScopes()->create($attributes)->fresh('group');
    }

    public function update(Setting $setting, array $attributes): Setting
    {
        $setting->update($attributes);

        return $setting->fresh('group');
    }

    public function delete(Setting $setting): void
    {
        $setting->delete();
    }

    public function allPublic(?int $schoolId = null): Collection
    {
        return Setting::withoutGlobalScopes()
            ->where('is_public', true)
            ->where(function (Builder $query) use ($schoolId): void {
                $query->where(function (Builder $global): void {
                    $global->where('scope', 'global')->whereNull('school_id');
                });

                if ($schoolId !== null) {
                    $query->orWhere(function (Builder $tenant) use ($schoolId): void {
                        $tenant->where('scope', 'tenant')->where('school_id', $schoolId);
                    });
                }
            })
            ->with('group')
            ->get()
            ->sortBy(fn (Setting $setting) => [$setting->key, $setting->school_id === $schoolId ? 0 : 1])
            ->unique('key')
            ->values();
    }

    protected function query(): Builder
    {
        return Setting::query()->with('group');
    }
}
