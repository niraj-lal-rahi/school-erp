<?php

namespace App\Models\Finance;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_fee_structures';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'school_class_id',
        'section_id',
        'name',
        'code',
        'description',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeStructureItem::class, 'fee_structure_id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(StudentFeeAssignment::class, 'fee_structure_id');
    }
}
