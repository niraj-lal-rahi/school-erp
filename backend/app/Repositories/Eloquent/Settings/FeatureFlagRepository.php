<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\FeatureFlag;
use App\Repositories\Contracts\Settings\FeatureFlagRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    public function list(array $filters = []): Collection
    {
        return $this->query()
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when(isset($filters['is_enabled']), fn (Builder $query) => $query->where('is_enabled', (bool) $filters['is_enabled']))
            ->orderBy('module')
            ->orderBy('feature_code')
            ->get();
    }

    public function findOrFail(int $id): FeatureFlag
    {
        return $this->query()->findOrFail($id);
    }

    public function findByCode(string $featureCode, string $module, ?int $schoolId = null): ?FeatureFlag
    {
        return FeatureFlag::withoutGlobalScopes()
            ->where('feature_code', $featureCode)
            ->where('module', $module)
            ->where(function (Builder $query) use ($schoolId): void {
                $query->whereNull('school_id');

                if ($schoolId !== null) {
                    $query->orWhere('school_id', $schoolId);
                }
            })
            ->orderByRaw($schoolId !== null ? "case when school_id = {$schoolId} then 0 else 1 end" : 'case when school_id is null then 0 else 1 end')
            ->first();
    }

    public function create(array $attributes): FeatureFlag
    {
        return FeatureFlag::withoutGlobalScopes()->create($attributes)->fresh();
    }

    public function update(FeatureFlag $featureFlag, array $attributes): FeatureFlag
    {
        $featureFlag->update($attributes);

        return $featureFlag->fresh();
    }

    protected function query(): Builder
    {
        return FeatureFlag::query();
    }
}
