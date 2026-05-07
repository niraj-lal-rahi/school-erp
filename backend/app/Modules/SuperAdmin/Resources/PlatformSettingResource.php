<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'setting_group' => $this->setting_group,
            'key' => $this->key,
            'value_type' => $this->value_type,
            'is_sensitive' => (bool) $this->is_sensitive,
            'is_public' => (bool) $this->is_public,
            'value' => $this->is_sensitive ? null : $this->typedValue(),
            'masked_value' => $this->maskedValue(),
            'has_value' => $this->value !== null,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'updated_at' => $this->updated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
