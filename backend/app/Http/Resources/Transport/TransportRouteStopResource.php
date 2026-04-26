<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportRouteStopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_id' => $this->route_id,
            'name' => $this->name,
            'code' => $this->code,
            'stop_order' => $this->stop_order,
            'pickup_time' => $this->pickup_time,
            'drop_time' => $this->drop_time,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance_from_start_km' => $this->distance_from_start_km,
            'address' => $this->address,
            'status' => $this->status,
            'route' => $this->whenLoaded('route', fn () => $this->route ? [
                'id' => $this->route->id,
                'name' => $this->route->name,
                'code' => $this->route->code,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
