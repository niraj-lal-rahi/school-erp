<?php

namespace App\Models\Transport;

use App\Models\Concerns\BelongsToSchool;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportTripLog extends Model
{
    use BelongsToSchool;

    protected $table = 'transport_trip_logs';

    protected $fillable = [
        'school_id',
        'transport_trip_id',
        'student_id',
        'staff_id',
        'route_stop_id',
        'user_type',
        'event_type',
        'event_time',
        'latitude',
        'longitude',
        'marked_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(TransportRouteStop::class, 'route_stop_id');
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
