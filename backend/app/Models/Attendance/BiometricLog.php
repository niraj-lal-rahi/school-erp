<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class BiometricLog extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_biometric_logs';

    protected $fillable = [
        'school_id',
        'device_id',
        'user_type',
        'user_id',
        'log_datetime',
        'log_type',
        'raw_data',
        'processed',
    ];

    protected function casts(): array
    {
        return [
            'log_datetime' => 'datetime',
            'raw_data' => 'array',
            'processed' => 'bool',
        ];
    }
}
