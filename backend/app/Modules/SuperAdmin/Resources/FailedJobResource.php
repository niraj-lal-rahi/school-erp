<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FailedJobResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'uuid' => $this->uuid ?? null,
            'connection' => $this->connection ?? null,
            'queue' => $this->queue ?? null,
            'failed_at' => $this->failed_at ?? null,
            'exception' => $this->exception ?? null,
            'payload_excerpt' => isset($this->payload)
                ? mb_substr((string) $this->payload, 0, 500)
                : null,
        ];
    }
}
