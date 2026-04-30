<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalContextResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => $this->resource['user'] ?? null,
            'profile_mode' => $this->resource['profile_mode'] ?? 'none',
            'available_profiles' => PortalProfileResource::collection(collect($this->resource['available_profiles'] ?? [])),
            'accessible_students' => $this->resource['accessible_students'] ?? [],
            'active_context' => $this->resource['active_context'] ?? null,
        ];
    }
}
