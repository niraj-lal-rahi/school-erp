<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendanceRecord extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'attendance_student_records';

    protected $fillable = [
        'school_id',
        'attendance_session_id',
        'student_id',
        'attendance_status_type_id',
        'check_in_time',
        'check_out_time',
        'remarks',
        'marked_by',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(StudentAttendanceSession::class, 'attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatusType::class, 'attendance_status_type_id');
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
