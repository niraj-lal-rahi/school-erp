<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportGpsLog extends Model
{
    use BelongsToSchool;

    protected $table = 'transport_gps_logs';

    protected $fillable = [
        'school_id',
        'vehicle_id',
        'transport_trip_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed_kmph',
        'heading_degree',
        'recorded_at',
        'engine_status',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'speed_kmph' => 'decimal:2',
            'heading_degree' => 'decimal:2',
            'recorded_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }
}
