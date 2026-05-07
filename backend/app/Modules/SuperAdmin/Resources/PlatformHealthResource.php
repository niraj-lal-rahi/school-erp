<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformHealthResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this['status'] ?? 'unknown',
            'checked_at' => $this['checked_at'] ?? now()->toISOString(),
            'components' => $this['components'] ?? [],
            'summary' => $this['summary'] ?? [],
        ];
    }
}
