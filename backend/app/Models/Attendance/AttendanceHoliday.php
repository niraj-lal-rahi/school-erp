<?php

namespace App\Models\Attendance;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceHoliday extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_holidays';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'applies_to',
        'school_class_id',
        'section_id',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_recurring' => 'bool',
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
}
