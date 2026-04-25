<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Timetable\TimetableEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendancePeriod extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'attendance_periods';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'sequence',
        'is_break',
        'break_type',
        'status',
    ];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(StudentAttendanceSession::class, 'attendance_period_id');
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'attendance_period_id');
    }
}
