<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportVehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_no' => $this->vehicle_no,
            'registration_no' => $this->registration_no,
            'name' => $this->name,
            'vehicle_type' => $this->vehicle_type,
            'make' => $this->make,
            'model' => $this->model,
            'color' => $this->color,
            'seat_capacity' => $this->seat_capacity,
            'fuel_type' => $this->fuel_type,
            'ownership_type' => $this->ownership_type,
            'gps_device_code' => $this->gps_device_code,
            'insurance_expiry_date' => optional($this->insurance_expiry_date)->toDateString(),
            'permit_expiry_date' => optional($this->permit_expiry_date)->toDateString(),
            'fitness_expiry_date' => optional($this->fitness_expiry_date)->toDateString(),
            'odometer_reading' => $this->odometer_reading,
            'status' => $this->status,
            'notes' => $this->notes,
            'route_assignments_count' => $this->whenCounted('routeAssignments'),
            'trips_count' => $this->whenCounted('trips'),
            'maintenance_logs_count' => $this->whenCounted('maintenanceLogs'),
            'fuel_logs_count' => $this->whenCounted('fuelLogs'),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
