<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemErrorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'file' => $this['file'] ?? null,
            'line_number' => $this['line_number'] ?? null,
            'level' => $this['level'] ?? 'error',
            'message' => $this['message'] ?? null,
            'logged_at' => $this['logged_at'] ?? null,
        ];
    }
}
