<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffAttendance extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'staff_attendance';

    protected $fillable = [
        'school_id',
        'staff_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'attendance_status',
        'source',
        'remarks',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
