<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRecipient extends Model
{
    use BelongsToSchool;

    protected $table = 'announcement_recipients';

    protected $fillable = [
        'school_id',
        'announcement_id',
        'recipient_type',
        'recipient_id',
        'read_at',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function recipientStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'recipient_id');
    }

    public function recipientGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'recipient_id');
    }

    public function recipientStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recipient_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function resolveRecipient()
    {
        return match ($this->recipient_type) {
            'student' => $this->recipientStudent,
            'guardian' => $this->recipientGuardian,
            'staff' => $this->recipientStaff,
            'user' => $this->recipientUser,
            default => null,
        };
    }
}
