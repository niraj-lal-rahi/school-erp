<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mode' => $this->resource['mode'] ?? null,
            'available_profiles' => PortalProfileResource::collection(collect($this->resource['available_profiles'] ?? [])),
            'accessible_students' => $this->resource['accessible_students'] ?? [],
            'active_context' => $this->resource['active_context'] ?? null,
            'dashboard' => $this->resource['dashboard'] ?? null,
        ];
    }
}
