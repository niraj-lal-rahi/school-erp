<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendancePeriod extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_periods';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'sequence',
        'status',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(StudentAttendanceSession::class, 'attendance_period_id');
    }
}
