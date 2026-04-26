<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use App\Models\HR\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportDriver extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'transport_drivers';

    protected $fillable = [
        'school_id',
        'staff_id',
        'driver_code',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'phone',
        'alternate_phone',
        'email',
        'license_no',
        'license_expiry_date',
        'date_of_birth',
        'joining_date',
        'status',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry_date' => 'date',
            'date_of_birth' => 'date',
            'joining_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function routeAssignments(): HasMany
    {
        return $this->hasMany(TransportRouteVehicleAssignment::class, 'driver_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class, 'driver_id');
    }

    public function gpsLogs(): HasMany
    {
        return $this->hasMany(TransportGpsLog::class, 'driver_id');
    }
}
