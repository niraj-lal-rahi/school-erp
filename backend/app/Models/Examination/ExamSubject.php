<?php

namespace App\Models\Examination;

use App\Models\AcademicManagement\Subject;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubject extends Model
{
    use BelongsToSchool;

    protected $table = 'exam_subjects';

    protected $fillable = [
        'school_id',
        'exam_id',
        'subject_id',
        'max_marks',
        'passing_marks',
        'weightage',
    ];

    protected function casts(): array
    {
        return [
            'max_marks' => 'decimal:2',
            'passing_marks' => 'decimal:2',
            'weightage' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
