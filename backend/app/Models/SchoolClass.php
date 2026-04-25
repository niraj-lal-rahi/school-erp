<?php

namespace App\Models;

use App\Models\AcademicManagement\AcademicCalendarEvent;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\AcademicManagement\Curriculum;
use App\Models\AcademicManagement\HomeworkAssignment;
use App\Models\AcademicManagement\LessonPlan;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'school_classes';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'uuid',
        'name',
        'code',
        'grade_level',
        'level_order',
        'sort_order',
        'description',
        'status',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function classSubjectAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectAssignment::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function curricula(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(LessonPlan::class);
    }

    public function homeworkAssignments(): HasMany
    {
        return $this->hasMany(HomeworkAssignment::class);
    }

    public function academicCalendarEvents(): HasMany
    {
        return $this->hasMany(AcademicCalendarEvent::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(StudentAttendanceSession::class);
    }
}
