<?php

namespace App\Http\Resources\Rbac;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'module' => $this->module,
            'action' => $this->action,
            'description' => $this->description,
            'is_system' => (bool) $this->is_system,
            'status' => $this->status,
            'roles_count' => $this->whenCounted('roles'),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
