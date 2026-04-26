<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportRouteStop extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'transport_route_stops';

    protected $fillable = [
        'school_id',
        'route_id',
        'name',
        'code',
        'stop_order',
        'pickup_time',
        'drop_time',
        'latitude',
        'longitude',
        'distance_from_start_km',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pickup_time' => 'datetime:H:i:s',
            'drop_time' => 'datetime:H:i:s',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'distance_from_start_km' => 'decimal:2',
            'stop_order' => 'integer',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function pickupStudentAllocations(): HasMany
    {
        return $this->hasMany(StudentTransportAllocation::class, 'pickup_stop_id');
    }

    public function dropStudentAllocations(): HasMany
    {
        return $this->hasMany(StudentTransportAllocation::class, 'drop_stop_id');
    }

    public function pickupStaffAllocations(): HasMany
    {
        return $this->hasMany(StaffTransportAllocation::class, 'pickup_stop_id');
    }

    public function dropStaffAllocations(): HasMany
    {
        return $this->hasMany(StaffTransportAllocation::class, 'drop_stop_id');
    }

    public function tripLogs(): HasMany
    {
        return $this->hasMany(TransportTripLog::class, 'route_stop_id');
    }
}
