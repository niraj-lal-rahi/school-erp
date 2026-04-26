<?php

namespace App\Http\Requests\Transport;

use App\Models\Transport\TransportVehicle;
use Illuminate\Validation\Rule;

class UpsertTransportVehicleRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $vehicle = $this->route('vehicle');
        $vehicleId = $vehicle instanceof TransportVehicle ? $vehicle->id : null;

        return [
            'vehicle_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('transport_vehicles', 'vehicle_no')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($vehicleId),
            ],
            'registration_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('transport_vehicles', 'registration_no')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($vehicleId),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'vehicle_type' => ['required', 'string', Rule::in(['bus', 'van', 'car', 'auto', 'other'])],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:40'],
            'seat_capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'fuel_type' => ['nullable', 'string', Rule::in(['diesel', 'petrol', 'cng', 'electric', 'hybrid', 'other'])],
            'ownership_type' => ['nullable', 'string', Rule::in(['owned', 'leased', 'contract'])],
            'gps_device_code' => ['nullable', 'string', 'max:80'],
            'insurance_expiry_date' => ['nullable', 'date'],
            'permit_expiry_date' => ['nullable', 'date'],
            'fitness_expiry_date' => ['nullable', 'date'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'maintenance', 'retired'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
