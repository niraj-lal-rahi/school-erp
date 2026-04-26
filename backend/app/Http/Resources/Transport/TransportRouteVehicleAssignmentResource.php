<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportRouteVehicleAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_id' => $this->route_id,
            'vehicle_id' => $this->vehicle_id,
            'driver_id' => $this->driver_id,
            'academic_year_id' => $this->academic_year_id,
            'assigned_from' => optional($this->assigned_from)->toDateString(),
            'assigned_to' => optional($this->assigned_to)->toDateString(),
            'shift_type' => $this->shift_type,
            'status' => $this->status,
            'notes' => $this->notes,
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
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'code' => $this->academicYear->code,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
