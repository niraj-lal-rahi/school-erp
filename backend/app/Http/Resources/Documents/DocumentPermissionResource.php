<?php

namespace App\Http\Resources\Documents;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentPermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'permission_type' => $this->permission_type,
            'permission_id' => $this->permission_id,
            'can_view' => (bool) $this->can_view,
            'can_download' => (bool) $this->can_download,
            'can_update' => (bool) $this->can_update,
            'can_delete' => (bool) $this->can_delete,
            'can_verify' => (bool) $this->can_verify,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'role' => $this->whenLoaded('role', fn () => $this->role ? [
                'id' => $this->role->id,
                'name' => $this->role->name,
                'code' => $this->role->code,
                'slug' => $this->role->slug,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
