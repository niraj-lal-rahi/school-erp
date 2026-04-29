<?php

namespace App\Models\Examination;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'student_results';

    protected $fillable = [
        'school_id',
        'exam_id',
        'student_id',
        'total_marks',
        'obtained_marks',
        'percentage',
        'grade',
        'gpa',
        'result_status',
        'rank',
        'remarks',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'decimal:2',
            'obtained_marks' => 'decimal:2',
            'percentage' => 'decimal:2',
            'gpa' => 'decimal:2',
            'computed_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function resultSubjectDetails(): HasMany
    {
        return $this->hasMany(ResultSubjectDetail::class, 'student_result_id');
    }
}
