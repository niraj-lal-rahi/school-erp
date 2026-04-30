<?php

namespace App\Models\Portal;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortalUserProfile extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id',
        'profile_type',
        'profile_id',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'profile_id');
    }

    public function guardianProfile(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'profile_id');
    }

    public function getProfileAttribute(): Student|Guardian|null
    {
        return match ($this->profile_type) {
            'student' => $this->relationLoaded('studentProfile') ? $this->studentProfile : $this->studentProfile()->first(),
            'guardian' => $this->relationLoaded('guardianProfile') ? $this->guardianProfile : $this->guardianProfile()->first(),
            default => null,
        };
    }
}
