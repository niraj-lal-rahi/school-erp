<?php

namespace App\Models\Transport;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRouteVehicleAssignment extends Model
{
    use BelongsToSchool;

    protected $table = 'transport_route_vehicle_assignments';

    protected $fillable = [
        'school_id',
        'route_id',
        'vehicle_id',
        'driver_id',
        'academic_year_id',
        'assigned_from',
        'assigned_to',
        'shift_type',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_from' => 'date',
            'assigned_to' => 'date',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function studentAllocations(): HasMany
    {
        return $this->hasMany(StudentTransportAllocation::class, 'route_vehicle_assignment_id');
    }

    public function staffAllocations(): HasMany
    {
        return $this->hasMany(StaffTransportAllocation::class, 'route_vehicle_assignment_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class, 'route_vehicle_assignment_id');
    }
}
