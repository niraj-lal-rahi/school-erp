<?php

namespace App\Http\Requests\Transport;

use App\Models\Transport\TransportRouteStop;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertTransportRouteStopRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $routeStop = $this->route('routeStop');
        $routeStopId = $routeStop instanceof TransportRouteStop ? $routeStop->id : null;
        $routeId = $this->input('route_id', $routeStop?->route_id);

        return [
            'route_id' => ['required', 'integer', Rule::exists('transport_routes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('transport_route_stops', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($routeStopId),
            ],
            'stop_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('transport_route_stops', 'stop_order')
                    ->where(fn ($query) => $query->where('route_id', $routeId))
                    ->ignore($routeStopId),
            ],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'drop_time' => ['nullable', 'date_format:H:i'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_from_start_km' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('pickup_time') && $this->filled('drop_time')
                && $this->input('drop_time') < $this->input('pickup_time')) {
                $validator->errors()->add('drop_time', 'Drop time must be after or equal to pickup time.');
            }
        });
    }
}
