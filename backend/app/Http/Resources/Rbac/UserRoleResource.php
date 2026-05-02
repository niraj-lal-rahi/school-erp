<?php

namespace App\Http\Resources\Rbac;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRoleResource extends JsonResource
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
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'assigned_by' => $this->assigned_by,
            'user' => $this->whenLoaded('user', function (): array {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                    'school_id' => $this->user?->school_id,
                ];
            }),
            'role' => $this->whenLoaded('role', function (): array {
                return [
                    'id' => $this->role?->id,
                    'name' => $this->role?->name,
                    'code' => $this->role?->code,
                    'role_type' => $this->role?->role_type,
                    'status' => $this->role?->status,
                ];
            }),
            'assigned_by_user' => $this->whenLoaded('assignedBy', function (): ?array {
                if (! $this->assignedBy) {
                    return null;
                }

                return [
                    'id' => $this->assignedBy->id,
                    'name' => $this->assignedBy->name,
                    'email' => $this->assignedBy->email,
                ];
            }),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
