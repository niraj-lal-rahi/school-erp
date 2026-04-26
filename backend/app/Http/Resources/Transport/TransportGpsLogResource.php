<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportGpsLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'transport_trip_id' => $this->transport_trip_id,
            'driver_id' => $this->driver_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'speed_kmph' => $this->speed_kmph,
            'heading_degree' => $this->heading_degree,
            'recorded_at' => optional($this->recorded_at)->toAtomString(),
            'engine_status' => $this->engine_status,
            'raw_payload' => $this->raw_payload,
            'vehicle' => $this->whenLoaded('vehicle', fn () => $this->vehicle ? [
                'id' => $this->vehicle->id,
                'vehicle_no' => $this->vehicle->vehicle_no,
                'registration_no' => $this->vehicle->registration_no,
                'name' => $this->vehicle->name,
            ] : null),
            'trip' => $this->whenLoaded('trip', fn () => $this->trip ? [
                'id' => $this->trip->id,
                'trip_date' => optional($this->trip->trip_date)->toDateString(),
                'trip_type' => $this->trip->trip_type,
                'status' => $this->trip->status,
            ] : null),
            'driver' => $this->whenLoaded('driver', fn () => $this->driver ? [
                'id' => $this->driver->id,
                'full_name' => $this->driver->full_name,
                'license_no' => $this->driver->license_no,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
