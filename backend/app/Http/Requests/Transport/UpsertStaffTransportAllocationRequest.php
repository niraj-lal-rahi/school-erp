<?php

namespace App\Http\Requests\Transport;

use App\Models\Transport\TransportRouteStop;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertStaffTransportAllocationRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'route_id' => ['required', 'integer', Rule::exists('transport_routes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'route_vehicle_assignment_id' => ['nullable', 'integer', Rule::exists('transport_route_vehicle_assignments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'pickup_stop_id' => ['nullable', 'integer', Rule::exists('transport_route_stops', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'drop_stop_id' => ['nullable', 'integer', Rule::exists('transport_route_stops', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'allocated_from' => ['required', 'date'],
            'allocated_to' => ['nullable', 'date', 'after_or_equal:allocated_from'],
            'fare_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'cancelled', 'completed'])],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $routeId = (int) $this->input('route_id');

            foreach (['pickup_stop_id', 'drop_stop_id'] as $field) {
                $stopId = (int) $this->input($field);
                if (! $stopId) {
                    continue;
                }

                $stop = TransportRouteStop::query()->find($stopId);
                if (! $stop || $stop->route_id !== $routeId) {
                    $validator->errors()->add($field, 'The selected stop must belong to the chosen route.');
                }
            }
        });
    }
}
