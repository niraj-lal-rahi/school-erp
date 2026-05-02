<?php

namespace App\Http\Resources\Rbac;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleAuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'role_id' => $this->role_id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'performed_by' => $this->performed_by,
            'ip_address' => $this->ip_address,
            'role' => $this->whenLoaded('role', function (): ?array {
                if (! $this->role) {
                    return null;
                }

                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                    'code' => $this->role->code,
                ];
            }),
            'user' => $this->whenLoaded('user', function (): ?array {
                if (! $this->user) {
                    return null;
                }

                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'performed_by_user' => $this->whenLoaded('performedBy', function (): ?array {
                if (! $this->performedBy) {
                    return null;
                }

                return [
                    'id' => $this->performedBy->id,
                    'name' => $this->performedBy->name,
                    'email' => $this->performedBy->email,
                ];
            }),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
