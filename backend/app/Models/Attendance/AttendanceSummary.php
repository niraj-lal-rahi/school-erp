<?php

namespace App\Models\Attendance;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_summary';

    protected $fillable = [
        'school_id',
        'user_type',
        'user_id',
        'academic_year_id',
        'total_days',
        'present_days',
        'absent_days',
        'leave_days',
        'late_days',
        'percentage',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'last_updated_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
