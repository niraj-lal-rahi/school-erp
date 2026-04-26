<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertTransportRouteVehicleAssignmentRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'route_id' => ['required', 'integer', Rule::exists('transport_routes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'vehicle_id' => ['required', 'integer', Rule::exists('transport_vehicles', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'driver_id' => ['nullable', 'integer', Rule::exists('transport_drivers', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'assigned_from' => ['required', 'date'],
            'assigned_to' => ['nullable', 'date', 'after_or_equal:assigned_from'],
            'shift_type' => ['required', 'string', Rule::in(['pickup', 'drop', 'both'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $routeId = (int) $this->input('route_id');
            $pickupStopId = (int) $this->input('pickup_stop_id');

            if ($pickupStopId && ! $routeId) {
                $validator->errors()->add('route_id', 'Route is required when stop data is provided.');
            }
        });
    }
}
