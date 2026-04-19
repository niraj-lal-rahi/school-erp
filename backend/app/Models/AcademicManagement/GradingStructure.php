<?php

namespace App\Models\AcademicManagement;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradingStructure extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'description',
        'pass_percentage',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pass_percentage' => 'decimal:2',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scaleItems(): HasMany
    {
        return $this->hasMany(GradeScaleItem::class);
    }
}
