<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;

class UpsertVehicleMaintenanceLogRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'vehicle_id' => ['required', 'integer', Rule::exists('transport_vehicles', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'maintenance_type' => ['required', 'string', Rule::in(['service', 'repair', 'inspection', 'insurance', 'permit', 'fitness', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'maintenance_date' => ['required', 'date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:maintenance_date'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
            'recorded_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
