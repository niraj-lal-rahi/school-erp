<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use BelongsToSchool;

    protected $table = 'notification_logs';

    protected $fillable = [
        'school_id',
        'notifiable_type',
        'notifiable_id',
        'channel',
        'template_id',
        'subject',
        'message',
        'provider',
        'provider_message_id',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function notifiableStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'notifiable_id');
    }

    public function notifiableGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'notifiable_id');
    }

    public function notifiableStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'notifiable_id');
    }

    public function notifiableUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    public function resolveNotifiable()
    {
        return match ($this->notifiable_type) {
            'student' => $this->notifiableStudent,
            'guardian' => $this->notifiableGuardian,
            'staff' => $this->notifiableStaff,
            'user' => $this->notifiableUser,
            default => null,
        };
    }
}
