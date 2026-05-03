<?php

namespace App\Services\Settings;

use App\Models\Settings\LocalizationSetting;
use App\Repositories\Contracts\Settings\LocalizationSettingRepositoryInterface;
use Illuminate\Http\Request;

class LocalizationService
{
    public function __construct(
        protected LocalizationSettingRepositoryInterface $localizations,
        protected SettingAuditService $audits,
    ) {
    }

    public function getForTenant(int $schoolId): ?LocalizationSetting
    {
        return $this->localizations->findByTenant($schoolId);
    }

    public function update(int $schoolId, array $attributes, $actor = null, ?Request $request = null): LocalizationSetting
    {
        $existing = $this->getForTenant($schoolId);
        $localization = $this->localizations->upsert($schoolId, $attributes);

        $this->audits->log('localization', 'localization', $existing?->toArray(), $localization->toArray(), $actor, $request, $schoolId);

        return $localization;
    }

    public function tenantConfig(int $schoolId): array
    {
        $localization = $this->getForTenant($schoolId);

        return [
            'timezone' => $localization?->timezone ?? 'Asia/Kolkata',
            'locale' => $localization?->locale ?? 'en',
            'date_format' => $localization?->date_format ?? 'd-m-Y',
            'time_format' => $localization?->time_format ?? 'h:i A',
            'currency' => $localization?->currency ?? 'INR',
            'currency_symbol' => $localization?->currency_symbol ?? '₹',
            'first_day_of_week' => $localization?->first_day_of_week ?? 'monday',
        ];
    }
}
