<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingAuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'setting_type' => $this->setting_type,
            'setting_key' => $this->setting_key,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'changed_by' => $this->changed_by,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'changed_by_user' => $this->whenLoaded('changedByUser', fn () => $this->changedByUser ? [
                'id' => $this->changedByUser->id,
                'name' => $this->changedByUser->name,
                'email' => $this->changedByUser->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
