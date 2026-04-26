<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertTransportTripRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'route_vehicle_assignment_id' => ['required', 'integer', Rule::exists('transport_route_vehicle_assignments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'route_id' => ['required', 'integer', Rule::exists('transport_routes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'vehicle_id' => ['required', 'integer', Rule::exists('transport_vehicles', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'driver_id' => ['nullable', 'integer', Rule::exists('transport_drivers', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'trip_date' => ['required', 'date'],
            'trip_type' => ['required', 'string', Rule::in(['pickup', 'drop', 'round_trip', 'special'])],
            'scheduled_start_time' => ['nullable', 'date_format:H:i'],
            'scheduled_end_time' => ['nullable', 'date_format:H:i', 'after:scheduled_start_time'],
            'status' => ['required', 'string', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('scheduled_start_time') xor $this->filled('scheduled_end_time')) {
                $validator->errors()->add('scheduled_end_time', 'Both scheduled start and end times should be supplied together.');
            }
        });
    }
}
