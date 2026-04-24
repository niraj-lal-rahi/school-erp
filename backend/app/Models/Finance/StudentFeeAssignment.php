<?php

namespace App\Models\Finance;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFeeAssignment extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_student_fee_assignments';

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'school_class_id',
        'section_id',
        'fee_structure_id',
        'assigned_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class, 'student_fee_assignment_id');
    }
}
