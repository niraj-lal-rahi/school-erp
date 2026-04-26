<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use BelongsToSchool;

    protected $table = 'notification_preferences';

    protected $fillable = [
        'school_id',
        'user_type',
        'user_id',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'in_app_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'quiet_hours_start' => 'datetime:H:i:s',
            'quiet_hours_end' => 'datetime:H:i:s',
        ];
    }

    public function userStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'user_id');
    }

    public function userGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'user_id');
    }

    public function userStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'user_id');
    }

    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolveOwner()
    {
        return match ($this->user_type) {
            'student' => $this->userStudent,
            'guardian' => $this->userGuardian,
            'staff' => $this->userStaff,
            'user' => $this->userAccount,
            default => null,
        };
    }
}
