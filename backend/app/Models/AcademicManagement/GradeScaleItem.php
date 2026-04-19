<?php

namespace App\Models\AcademicManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScaleItem extends Model
{
    protected $fillable = [
        'grading_structure_id',
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

    public function gradingStructure(): BelongsTo
    {
        return $this->belongsTo(GradingStructure::class);
    }
}
