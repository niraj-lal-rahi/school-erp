<?php

namespace App\Models\Examination;

use App\Models\AcademicManagement\Subject;
use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamMark extends Model
{
    use BelongsToSchool;

    protected $table = 'exam_marks';

    protected $fillable = [
        'school_id',
        'exam_id',
        'student_id',
        'subject_id',
        'marks_obtained',
        'is_absent',
        'remarks',
        'evaluated_by',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
            'is_absent' => 'bool',
            'evaluated_at' => 'datetime',
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
