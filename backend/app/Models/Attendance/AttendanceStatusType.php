<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceStatusType extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'attendance_status_types';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'is_present',
        'counts_for_attendance',
        'color_code',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_present' => 'bool',
            'counts_for_attendance' => 'bool',
        ];
    }

    public function studentRecords(): HasMany
    {
        return $this->hasMany(StudentAttendanceRecord::class, 'attendance_status_type_id');
    }
}
