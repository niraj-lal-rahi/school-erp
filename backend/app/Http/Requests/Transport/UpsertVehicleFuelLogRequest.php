<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertVehicleFuelLogRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'vehicle_id' => ['required', 'integer', Rule::exists('transport_vehicles', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'fuel_date' => ['required', 'date'],
            'quantity_liters' => ['required', 'numeric', 'gt:0'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'fuel_station' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:80'],
            'recorded_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('cost_per_unit') && $this->filled('quantity_liters')) {
                $expected = round((float) $this->input('cost_per_unit') * (float) $this->input('quantity_liters'), 2);
                $actual = round((float) $this->input('total_cost'), 2);

                if (abs($expected - $actual) > 0.1) {
                    $validator->errors()->add('total_cost', 'Total cost should match quantity multiplied by cost per unit.');
                }
            }
        });
    }
}
