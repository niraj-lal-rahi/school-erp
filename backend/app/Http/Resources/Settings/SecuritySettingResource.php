<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecuritySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'password_min_length' => $this->password_min_length,
            'password_requires_uppercase' => (bool) $this->password_requires_uppercase,
            'password_requires_number' => (bool) $this->password_requires_number,
            'password_requires_symbol' => (bool) $this->password_requires_symbol,
            'session_timeout_minutes' => $this->session_timeout_minutes,
            'max_login_attempts' => $this->max_login_attempts,
            'lockout_minutes' => $this->lockout_minutes,
            'two_factor_enabled' => (bool) $this->two_factor_enabled,
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
