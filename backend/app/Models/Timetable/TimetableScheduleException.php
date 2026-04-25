<?php

namespace App\Models\Timetable;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableScheduleException extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'timetable_schedule_exceptions';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'school_class_id',
        'section_id',
        'exception_date',
        'title',
        'description',
        'exception_type',
        'affects_attendance',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'affects_attendance' => 'bool',
    ];

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
}
