<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'relationship_type',
        'occupation',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot(['school_id', 'relationship', 'is_primary', 'is_emergency_contact', 'pickup_authorized'])
            ->withTimestamps();
    }
}
