<?php

namespace App\Models\Examination;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradingSystem extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'grading_systems';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'grading_type',
        'pass_percentage',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pass_percentage' => 'decimal:2',
        ];
    }

    public function gradeScales(): HasMany
    {
        return $this->hasMany(GradeScale::class, 'grading_system_id');
    }
}
