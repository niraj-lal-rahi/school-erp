<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTransportGpsLogRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'vehicle_id' => ['nullable', 'integer', Rule::exists('transport_vehicles', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'transport_trip_id' => ['nullable', 'integer', Rule::exists('transport_trips', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'driver_id' => ['nullable', 'integer', Rule::exists('transport_drivers', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmph' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'heading_degree' => ['nullable', 'numeric', 'between:0,360'],
            'recorded_at' => ['required', 'date'],
            'engine_status' => ['nullable', 'string', Rule::in(['on', 'off', 'idle'])],
            'raw_payload' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->filled('vehicle_id') && ! $this->filled('transport_trip_id') && ! $this->filled('driver_id')) {
                $validator->errors()->add('vehicle_id', 'At least one transport reference is required.');
            }
        });
    }
}
