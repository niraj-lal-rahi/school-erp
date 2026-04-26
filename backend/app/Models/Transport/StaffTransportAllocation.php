<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use App\Models\HR\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTransportAllocation extends Model
{
    use BelongsToSchool;

    protected $table = 'staff_transport_allocations';

    protected $fillable = [
        'school_id',
        'staff_id',
        'route_id',
        'route_vehicle_assignment_id',
        'pickup_stop_id',
        'drop_stop_id',
        'allocated_from',
        'allocated_to',
        'fare_amount',
        'status',
        'active_scope_key',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'allocated_from' => 'date',
            'allocated_to' => 'date',
            'fare_amount' => 'decimal:2',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function routeAssignment(): BelongsTo
    {
        return $this->belongsTo(TransportRouteVehicleAssignment::class, 'route_vehicle_assignment_id');
    }

    public function pickupStop(): BelongsTo
    {
        return $this->belongsTo(TransportRouteStop::class, 'pickup_stop_id');
    }

    public function dropStop(): BelongsTo
    {
        return $this->belongsTo(TransportRouteStop::class, 'drop_stop_id');
    }
}
