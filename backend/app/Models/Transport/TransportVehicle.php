<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportVehicle extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'transport_vehicles';

    protected $fillable = [
        'school_id',
        'vehicle_no',
        'registration_no',
        'name',
        'vehicle_type',
        'make',
        'model',
        'color',
        'seat_capacity',
        'fuel_type',
        'ownership_type',
        'gps_device_code',
        'insurance_expiry_date',
        'permit_expiry_date',
        'fitness_expiry_date',
        'odometer_reading',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'seat_capacity' => 'integer',
            'insurance_expiry_date' => 'date',
            'permit_expiry_date' => 'date',
            'fitness_expiry_date' => 'date',
            'odometer_reading' => 'integer',
        ];
    }

    public function routeAssignments(): HasMany
    {
        return $this->hasMany(TransportRouteVehicleAssignment::class, 'vehicle_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class, 'vehicle_id');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceLog::class, 'vehicle_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(VehicleFuelLog::class, 'vehicle_id');
    }

    public function gpsLogs(): HasMany
    {
        return $this->hasMany(TransportGpsLog::class, 'vehicle_id');
    }
}
