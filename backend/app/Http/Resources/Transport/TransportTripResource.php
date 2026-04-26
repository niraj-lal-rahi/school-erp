<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportTripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_vehicle_assignment_id' => $this->route_vehicle_assignment_id,
            'route_id' => $this->route_id,
            'vehicle_id' => $this->vehicle_id,
            'driver_id' => $this->driver_id,
            'trip_date' => optional($this->trip_date)->toDateString(),
            'trip_type' => $this->trip_type,
            'scheduled_start_time' => $this->scheduled_start_time,
            'scheduled_end_time' => $this->scheduled_end_time,
            'started_at' => optional($this->started_at)->toAtomString(),
            'completed_at' => optional($this->completed_at)->toAtomString(),
            'status' => $this->status,
            'total_boarded' => $this->total_boarded,
            'total_dropped' => $this->total_dropped,
            'notes' => $this->notes,
            'route_assignment' => new TransportRouteVehicleAssignmentResource($this->whenLoaded('routeAssignment')),
            'route' => $this->whenLoaded('route', fn () => $this->route ? [
                'id' => $this->route->id,
                'name' => $this->route->name,
                'code' => $this->route->code,
            ] : null),
            'vehicle' => $this->whenLoaded('vehicle', fn () => $this->vehicle ? [
                'id' => $this->vehicle->id,
                'vehicle_no' => $this->vehicle->vehicle_no,
                'registration_no' => $this->vehicle->registration_no,
                'name' => $this->vehicle->name,
            ] : null),
            'driver' => $this->whenLoaded('driver', fn () => $this->driver ? [
                'id' => $this->driver->id,
                'full_name' => $this->driver->full_name,
                'license_no' => $this->driver->license_no,
            ] : null),
            'trip_logs_count' => $this->whenCounted('tripLogs'),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
