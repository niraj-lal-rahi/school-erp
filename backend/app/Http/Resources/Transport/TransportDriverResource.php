<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportDriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'driver_code' => $this->driver_code,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'email' => $this->email,
            'license_no' => $this->license_no,
            'license_expiry_date' => optional($this->license_expiry_date)->toDateString(),
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'joining_date' => optional($this->joining_date)->toDateString(),
            'status' => $this->status,
            'address' => $this->address,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'notes' => $this->notes,
            'staff' => $this->whenLoaded('staff', fn () => $this->staff ? [
                'id' => $this->staff->id,
                'full_name' => $this->staff->full_name,
                'employee_code' => $this->staff->employee_code,
            ] : null),
            'route_assignments_count' => $this->whenCounted('routeAssignments'),
            'trips_count' => $this->whenCounted('trips'),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
