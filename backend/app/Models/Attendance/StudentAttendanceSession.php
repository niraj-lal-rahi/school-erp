<?php

namespace App\Models\Attendance;

use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\HR\Staff;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendanceSession extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'attendance_student_sessions';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'school_class_id',
        'section_id',
        'attendance_date',
        'session_type',
        'attendance_period_id',
        'session_slot',
        'subject_id',
        'teacher_id',
        'status',
        'marked_by',
        'submitted_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AttendancePeriod::class, 'attendance_period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(StudentAttendanceRecord::class, 'attendance_session_id');
    }
}
