<?php

namespace App\Models\Examination;

use App\Models\AcademicManagement\Subject;
use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevaluationRequest extends Model
{
    use BelongsToSchool;

    protected $table = 'revaluation_requests';

    protected $fillable = [
        'school_id',
        'exam_id',
        'student_id',
        'subject_id',
        'reason',
        'status',
        'requested_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'resolved_at' => 'datetime',
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
}
