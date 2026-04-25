<?php

namespace App\Models\Timetable;

use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\Attendance\AttendancePeriod;
use App\Models\Concerns\BelongsToSchool;
use App\Models\HR\Staff;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableEntry extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'timetable_entries';

    protected $fillable = [
        'school_id',
        'timetable_version_id',
        'academic_year_id',
        'school_class_id',
        'section_id',
        'day_of_week',
        'attendance_period_id',
        'subject_id',
        'staff_id',
        'room_id',
        'entry_type',
        'notes',
        'status',
    ];

    public function timetableVersion(): BelongsTo
    {
        return $this->belongsTo(TimetableVersion::class);
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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(TimetableRoom::class, 'room_id');
    }

    public function substitutions(): HasMany
    {
        return $this->hasMany(TimetableSubstitution::class);
    }
}
