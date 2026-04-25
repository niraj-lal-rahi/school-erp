<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_corrections';

    protected $fillable = [
        'school_id',
        'reference_type',
        'reference_id',
        'attendance_date',
        'old_status_id',
        'new_status_id',
        'reason',
        'approved_by',
        'approved_at',
        'status',
        'review_remarks',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function oldStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatusType::class, 'old_status_id');
    }

    public function newStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatusType::class, 'new_status_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
