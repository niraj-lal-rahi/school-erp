<?php

namespace App\Services\Settings;

use App\Models\Settings\IntegrationSetting;
use App\Repositories\Contracts\Settings\IntegrationSettingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class IntegrationSettingService
{
    public function __construct(
        protected IntegrationSettingRepositoryInterface $integrations,
        protected SettingAuditService $audits,
    ) {
    }

    public function list(array $filters = []): Collection
    {
        return $this->integrations->list($filters)->map(fn (IntegrationSetting $setting) => $this->sanitize($setting));
    }

    public function findOrFail(int $id): IntegrationSetting
    {
        return $this->sanitize($this->integrations->findOrFail($id));
    }

    public function create(array $attributes, $actor = null, ?Request $request = null): IntegrationSetting
    {
        $attributes = $this->prepareAttributes($attributes);
        $integration = $this->integrations->create($attributes);

        $this->audits->log('integration', $integration->integration_type, null, $this->sanitize($integration)->toArray(), $actor, $request, $integration->school_id, true);

        return $this->sanitize($integration);
    }

    public function update(IntegrationSetting $integration, array $attributes, $actor = null, ?Request $request = null): IntegrationSetting
    {
        $original = $integration->toArray();
        $updated = $this->integrations->update($integration, $this->prepareAttributes($attributes));

        $this->audits->log('integration', $updated->integration_type, $original, $this->sanitize($updated)->toArray(), $actor, $request, $updated->school_id, true);

        return $this->sanitize($updated);
    }

    public function delete(IntegrationSetting $integration, $actor = null, ?Request $request = null): void
    {
        $this->audits->log('integration', $integration->integration_type, $this->sanitize($integration)->toArray(), null, $actor, $request, $integration->school_id, true);
        $this->integrations->delete($integration);
    }

    public function safeConfig(IntegrationSetting $integration): array
    {
        return [
            'id' => $integration->id,
            'school_id' => $integration->school_id,
            'integration_type' => $integration->integration_type,
            'provider' => $integration->provider,
            'config' => $integration->safeConfig(),
            'status' => $integration->status,
        ];
    }

    protected function prepareAttributes(array $attributes): array
    {
        if (array_key_exists('encrypted_config', $attributes) && is_array($attributes['encrypted_config'])) {
            $attributes['encrypted_config'] = Crypt::encryptString(json_encode($attributes['encrypted_config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $attributes;
    }

    protected function sanitize(IntegrationSetting $integration): IntegrationSetting
    {
        $integration->setAttribute('encrypted_config', null);

        return $integration;
    }
}
