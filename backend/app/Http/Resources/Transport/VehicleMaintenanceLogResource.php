<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleMaintenanceLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'maintenance_type' => $this->maintenance_type,
            'title' => $this->title,
            'description' => $this->description,
            'maintenance_date' => optional($this->maintenance_date)->toDateString(),
            'next_due_date' => optional($this->next_due_date)->toDateString(),
            'odometer_reading' => $this->odometer_reading,
            'cost' => $this->cost,
            'vendor_name' => $this->vendor_name,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'vehicle' => $this->whenLoaded('vehicle', fn () => $this->vehicle ? [
                'id' => $this->vehicle->id,
                'vehicle_no' => $this->vehicle->vehicle_no,
                'registration_no' => $this->vehicle->registration_no,
                'name' => $this->vehicle->name,
            ] : null),
            'recorded_by' => $this->whenLoaded('recorder', fn () => $this->recorder ? [
                'id' => $this->recorder->id,
                'name' => $this->recorder->name,
                'email' => $this->recorder->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
