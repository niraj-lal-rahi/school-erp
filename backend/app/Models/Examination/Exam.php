<?php

namespace App\Models\Examination;

use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'exams';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'code',
        'exam_type_id',
        'term_id',
        'class_id',
        'section_id',
        'start_date',
        'end_date',
        'total_marks',
        'passing_marks',
        'result_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_marks' => 'decimal:2',
            'passing_marks' => 'decimal:2',
        ];
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function examSubjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class, 'exam_id');
    }

    public function studentExamEnrollments(): HasMany
    {
        return $this->hasMany(StudentExamEnrollment::class, 'exam_id');
    }

    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'exam_id');
    }

    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResult::class, 'exam_id');
    }

    public function resultPublication(): HasOne
    {
        return $this->hasOne(ResultPublication::class, 'exam_id');
    }

    public function revaluationRequests(): HasMany
    {
        return $this->hasMany(RevaluationRequest::class, 'exam_id');
    }
}
