<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenanceLog extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'vehicle_maintenance_logs';

    protected $fillable = [
        'school_id',
        'vehicle_id',
        'maintenance_type',
        'title',
        'description',
        'maintenance_date',
        'next_due_date',
        'odometer_reading',
        'cost',
        'vendor_name',
        'status',
        'recorded_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'next_due_date' => 'date',
            'odometer_reading' => 'integer',
            'cost' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
