<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\FeatureFlag;
use Illuminate\Support\Collection;

interface FeatureFlagRepositoryInterface
{
    public function list(array $filters = []): Collection;

    public function findOrFail(int $id): FeatureFlag;

    public function findByCode(string $featureCode, string $module, ?int $schoolId = null): ?FeatureFlag;

    public function create(array $attributes): FeatureFlag;

    public function update(FeatureFlag $featureFlag, array $attributes): FeatureFlag;
}
