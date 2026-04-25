<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BiometricLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'log_datetime' => $this->log_datetime?->toISOString(),
            'log_type' => $this->log_type,
            'raw_data' => $this->raw_data,
            'processed' => (bool) $this->processed,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
