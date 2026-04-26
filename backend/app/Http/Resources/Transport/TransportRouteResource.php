<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportRouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'route_type' => $this->route_type,
            'start_location' => $this->start_location,
            'end_location' => $this->end_location,
            'distance_km' => $this->distance_km,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'status' => $this->status,
            'description' => $this->description,
            'stops' => TransportRouteStopResource::collection($this->whenLoaded('stops')),
            'stops_count' => $this->whenCounted('stops'),
            'route_assignments_count' => $this->whenCounted('routeAssignments'),
            'student_allocations_count' => $this->whenCounted('studentAllocations'),
            'staff_allocations_count' => $this->whenCounted('staffAllocations'),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
