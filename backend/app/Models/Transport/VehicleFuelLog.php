<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleFuelLog extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'vehicle_fuel_logs';

    protected $fillable = [
        'school_id',
        'vehicle_id',
        'fuel_date',
        'quantity_liters',
        'cost_per_unit',
        'total_cost',
        'odometer_reading',
        'fuel_station',
        'reference_no',
        'recorded_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'fuel_date' => 'date',
            'quantity_liters' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'odometer_reading' => 'integer',
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
