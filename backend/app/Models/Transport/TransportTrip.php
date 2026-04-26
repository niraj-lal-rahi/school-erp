<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportTrip extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'transport_trips';

    protected $fillable = [
        'school_id',
        'route_vehicle_assignment_id',
        'route_id',
        'vehicle_id',
        'driver_id',
        'trip_date',
        'trip_type',
        'scheduled_start_time',
        'scheduled_end_time',
        'started_at',
        'completed_at',
        'status',
        'total_boarded',
        'total_dropped',
        'started_by',
        'completed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'scheduled_start_time' => 'datetime:H:i:s',
            'scheduled_end_time' => 'datetime:H:i:s',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_boarded' => 'integer',
            'total_dropped' => 'integer',
        ];
    }

    public function routeAssignment(): BelongsTo
    {
        return $this->belongsTo(TransportRouteVehicleAssignment::class, 'route_vehicle_assignment_id');
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

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function tripLogs(): HasMany
    {
        return $this->hasMany(TransportTripLog::class, 'transport_trip_id');
    }

    public function gpsLogs(): HasMany
    {
        return $this->hasMany(TransportGpsLog::class, 'transport_trip_id');
    }
}
