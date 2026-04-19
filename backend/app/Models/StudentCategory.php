<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCategory extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'uuid',
        'name',
        'code',
        'description',
        'status',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'category_id');
    }
}
