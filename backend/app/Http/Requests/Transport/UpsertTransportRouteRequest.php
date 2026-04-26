<?php

namespace App\Http\Requests\Transport;

use App\Models\Transport\TransportRoute;
use Illuminate\Validation\Rule;

class UpsertTransportRouteRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $route = $this->route('route');
        $routeId = $route instanceof TransportRoute ? $route->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('transport_routes', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($routeId),
            ],
            'route_type' => ['nullable', 'string', Rule::in(['regular', 'special', 'event', 'exam'])],
            'start_location' => ['nullable', 'string', 'max:255'],
            'end_location' => ['nullable', 'string', 'max:255'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'description' => ['nullable', 'string'],
        ];
    }
}
