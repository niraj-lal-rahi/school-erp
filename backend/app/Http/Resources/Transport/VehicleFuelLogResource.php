<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleFuelLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'fuel_date' => optional($this->fuel_date)->toDateString(),
            'quantity_liters' => $this->quantity_liters,
            'cost_per_unit' => $this->cost_per_unit,
            'total_cost' => $this->total_cost,
            'odometer_reading' => $this->odometer_reading,
            'fuel_station' => $this->fuel_station,
            'reference_no' => $this->reference_no,
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
