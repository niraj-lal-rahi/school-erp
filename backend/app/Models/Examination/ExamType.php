<?php

namespace App\Models\Examination;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamType extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'exam_types';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_type_id');
    }
}
