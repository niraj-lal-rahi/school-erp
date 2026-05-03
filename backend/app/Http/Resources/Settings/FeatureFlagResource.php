<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureFlagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'feature_code' => $this->feature_code,
            'module' => $this->module,
            'name' => $this->name,
            'description' => $this->description,
            'is_enabled' => (bool) $this->is_enabled,
            'rollout_percentage' => $this->rollout_percentage,
            'config' => $this->config ?? [],
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
