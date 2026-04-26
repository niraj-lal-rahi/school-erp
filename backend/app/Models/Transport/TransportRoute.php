<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportRoute extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'transport_routes';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'route_type',
        'start_location',
        'end_location',
        'distance_km',
        'estimated_duration_minutes',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
        ];
    }

    public function stops(): HasMany
    {
        return $this->hasMany(TransportRouteStop::class, 'route_id')->orderBy('stop_order');
    }

    public function routeAssignments(): HasMany
    {
        return $this->hasMany(TransportRouteVehicleAssignment::class, 'route_id');
    }

    public function studentAllocations(): HasMany
    {
        return $this->hasMany(StudentTransportAllocation::class, 'route_id');
    }

    public function staffAllocations(): HasMany
    {
        return $this->hasMany(StaffTransportAllocation::class, 'route_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class, 'route_id');
    }
}
