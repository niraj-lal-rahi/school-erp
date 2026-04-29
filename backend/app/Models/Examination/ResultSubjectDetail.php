<?php

namespace App\Models\Examination;

use App\Models\AcademicManagement\Subject;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultSubjectDetail extends Model
{
    use BelongsToSchool;

    protected $table = 'result_subject_details';

    protected $fillable = [
        'school_id',
        'student_result_id',
        'subject_id',
        'max_marks',
        'obtained_marks',
        'grade',
        'is_pass',
    ];

    protected function casts(): array
    {
        return [
            'max_marks' => 'decimal:2',
            'obtained_marks' => 'decimal:2',
            'is_pass' => 'bool',
        ];
    }

    public function studentResult(): BelongsTo
    {
        return $this->belongsTo(StudentResult::class, 'student_result_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
