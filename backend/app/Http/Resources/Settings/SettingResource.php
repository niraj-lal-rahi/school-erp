<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'group_id' => $this->group_id,
            'key' => $this->key,
            'value' => $this->is_sensitive ? null : $this->value,
            'value_type' => $this->value_type,
            'scope' => $this->scope,
            'is_sensitive' => (bool) $this->is_sensitive,
            'is_public' => (bool) $this->is_public,
            'description' => $this->description,
            'group' => $this->whenLoaded('group', fn () => $this->group ? new SettingGroupResource($this->group) : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
