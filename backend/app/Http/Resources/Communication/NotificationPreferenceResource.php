<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $owner = $this->resolveOwner();

        return [
            'id' => $this->id,
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'email_enabled' => (bool) $this->email_enabled,
            'sms_enabled' => (bool) $this->sms_enabled,
            'push_enabled' => (bool) $this->push_enabled,
            'in_app_enabled' => (bool) $this->in_app_enabled,
            'quiet_hours_start' => optional($this->quiet_hours_start)->format('H:i:s'),
            'quiet_hours_end' => optional($this->quiet_hours_end)->format('H:i:s'),
            'owner' => $owner ? [
                'id' => $owner->id,
                'name' => $owner->full_name ?? $owner->name ?? trim(($owner->first_name ?? '').' '.($owner->last_name ?? '')),
                'email' => $owner->email ?? null,
                'phone' => $owner->phone ?? null,
            ] : null,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
