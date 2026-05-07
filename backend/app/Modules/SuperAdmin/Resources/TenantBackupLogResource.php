<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantBackupLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'backup_type' => $this->backup_type,
            'storage_disk' => $this->storage_disk,
            'file_path' => $this->file_path,
            'file_size_bytes' => $this->file_size_bytes,
            'checksum' => $this->checksum,
            'is_encrypted' => (bool) $this->is_encrypted,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'error_message' => $this->error_message,
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
