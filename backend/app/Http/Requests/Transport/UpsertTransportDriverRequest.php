<?php

namespace App\Http\Requests\Transport;

use App\Models\Transport\TransportDriver;
use Illuminate\Validation\Rule;

class UpsertTransportDriverRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $driver = $this->route('driver');
        $driverId = $driver instanceof TransportDriver ? $driver->id : null;

        return [
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'driver_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('transport_drivers', 'driver_code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($driverId),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'license_no' => [
                'required',
                'string',
                'max:80',
                Rule::unique('transport_drivers', 'license_no')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($driverId),
            ],
            'license_expiry_date' => ['nullable', 'date'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'suspended', 'retired'])],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
