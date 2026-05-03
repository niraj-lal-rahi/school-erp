<?php

namespace App\Services\Settings;

use App\Models\Settings\BrandingSetting;
use App\Repositories\Contracts\Settings\BrandingSettingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    public function __construct(
        protected BrandingSettingRepositoryInterface $branding,
        protected SettingAuditService $audits,
    ) {
    }

    public function getForTenant(int $schoolId): ?BrandingSetting
    {
        return $this->branding->findByTenant($schoolId);
    }

    public function update(int $schoolId, array $attributes, $actor = null, ?Request $request = null): BrandingSetting
    {
        $existing = $this->branding->findByTenant($schoolId);

        if (($attributes['logo'] ?? null) instanceof UploadedFile) {
            $attributes['logo_path'] = $attributes['logo']->store("schools/{$schoolId}/branding", 'public');
        }

        if (($attributes['favicon'] ?? null) instanceof UploadedFile) {
            $attributes['favicon_path'] = $attributes['favicon']->store("schools/{$schoolId}/branding", 'public');
        }

        unset($attributes['logo'], $attributes['favicon']);

        $branding = $this->branding->upsert($schoolId, $attributes);
        $this->audits->log('branding', 'branding', $existing?->toArray(), $branding->toArray(), $actor, $request, $schoolId);

        return $branding;
    }

    public function publicConfig(int $schoolId): array
    {
        $branding = $this->getForTenant($schoolId);

        return [
            'school_name' => $branding?->school_name,
            'logo_url' => $branding?->logo_path ? Storage::disk('public')->url($branding->logo_path) : null,
            'favicon_url' => $branding?->favicon_path ? Storage::disk('public')->url($branding->favicon_path) : null,
            'primary_color' => $branding?->primary_color,
            'secondary_color' => $branding?->secondary_color,
            'accent_color' => $branding?->accent_color,
            'footer_text' => $branding?->footer_text,
        ];
    }
}
