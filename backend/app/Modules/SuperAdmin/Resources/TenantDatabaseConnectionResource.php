<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantDatabaseConnectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'connection_name' => $this->connection_name,
            'database_name' => $this->database_name,
            'database_driver' => $this->database_driver,
            'is_active' => (bool) $this->is_active,
            'connection_status' => $this->connection_status,
            'last_connected_at' => $this->last_connected_at?->toISOString(),
            'credentials' => [
                'host' => $this->resource->maskCredential($this->database_host, 3, 2),
                'port' => $this->resource->maskCredential($this->database_port, 0, 0),
                'username' => $this->resource->maskCredential($this->database_username, 2, 2),
                'password' => $this->resource->hasStoredPassword() ? '********' : null,
                'password_configured' => $this->resource->hasStoredPassword(),
            ],
            'rotation' => [
                'supported' => true,
                'active_connection_name' => $this->connection_name,
                'credential_record_id' => $this->id,
                'last_connected_at' => $this->last_connected_at?->toISOString(),
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
