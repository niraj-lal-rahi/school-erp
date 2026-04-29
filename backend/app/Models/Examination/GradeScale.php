<?php

namespace App\Models\Examination;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScale extends Model
{
    use BelongsToSchool;

    protected $table = 'grade_scales';

    protected $fillable = [
        'school_id',
        'grading_system_id',
        'grade_label',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'min_percentage' => 'decimal:2',
            'max_percentage' => 'decimal:2',
            'grade_point' => 'decimal:2',
        ];
    }

    public function gradingSystem(): BelongsTo
    {
        return $this->belongsTo(GradingSystem::class, 'grading_system_id');
    }
}
