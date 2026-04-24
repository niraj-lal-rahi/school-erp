<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'hr_departments';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
