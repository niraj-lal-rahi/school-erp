<?php

namespace App\Models\Portal;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalSession extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'active_profile_type',
        'active_profile_id',
        'active_student_id',
        'last_seen_at',
        'device_info',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'device_info' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'active_student_id');
    }

    public function activeStudentProfile(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'active_profile_id');
    }

    public function activeGuardianProfile(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'active_profile_id');
    }

    public function getActiveProfileAttribute(): Student|Guardian|null
    {
        return match ($this->active_profile_type) {
            'student' => $this->relationLoaded('activeStudentProfile') ? $this->activeStudentProfile : $this->activeStudentProfile()->first(),
            'guardian' => $this->relationLoaded('activeGuardianProfile') ? $this->activeGuardianProfile : $this->activeGuardianProfile()->first(),
            default => null,
        };
    }
}
