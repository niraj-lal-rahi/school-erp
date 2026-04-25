<?php

namespace App\Models;

use App\Models\AcademicManagement\AcademicCalendarEvent;
use App\Models\AcademicManagement\ClassSubjectAssignment;
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

class Section extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'uuid',
        'name',
        'code',
        'capacity',
        'class_teacher_id',
        'status',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function classSubjectAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectAssignment::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
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
