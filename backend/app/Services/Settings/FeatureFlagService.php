<?php

namespace App\Services\Settings;

use App\Models\Settings\FeatureFlag;
use App\Repositories\Contracts\Settings\FeatureFlagRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    public function __construct(
        protected FeatureFlagRepositoryInterface $features,
        protected SettingAuditService $audits,
    ) {
    }

    public function list(array $filters = []): Collection
    {
        return $this->features->list($filters);
    }

    public function findOrFail(int $id): FeatureFlag
    {
        return $this->features->findOrFail($id);
    }

    public function checkFeatureEnabled(string $featureCode, string $module, ?int $schoolId = null): bool
    {
        return Cache::remember(
            sprintf('feature-flags.%s.%s.%s', $schoolId ?? 'global', $module, $featureCode),
            now()->addMinutes(15),
            function () use ($featureCode, $module, $schoolId): bool {
                $feature = $this->features->findByCode($featureCode, $module, $schoolId);

                if ($feature === null) {
                    return false;
                }

                if (! $feature->is_enabled) {
                    return false;
                }

                if ($feature->rollout_percentage === null) {
                    return true;
                }

                return $feature->rollout_percentage >= 100;
            }
        );
    }

    public function update(FeatureFlag $featureFlag, array $attributes, $actor = null, ?Request $request = null): FeatureFlag
    {
        $updated = $this->features->update($featureFlag, $attributes);
        $this->clearCache($updated);
        $this->audits->log('feature_flag', $updated->feature_code, $featureFlag->toArray(), $updated->toArray(), $actor, $request, $updated->school_id);

        return $updated;
    }

    public function enable(FeatureFlag $featureFlag, $actor = null, ?Request $request = null): FeatureFlag
    {
        return $this->update($featureFlag, ['is_enabled' => true], $actor, $request);
    }

    public function disable(FeatureFlag $featureFlag, $actor = null, ?Request $request = null): FeatureFlag
    {
        return $this->update($featureFlag, ['is_enabled' => false], $actor, $request);
    }

    protected function clearCache(FeatureFlag $featureFlag): void
    {
        Cache::forget(sprintf('feature-flags.%s.%s.%s', $featureFlag->school_id ?? 'global', $featureFlag->module, $featureFlag->feature_code));
    }
}
