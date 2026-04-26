<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationMessage extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'communication_messages';

    protected $fillable = [
        'school_id',
        'conversation_id',
        'sender_type',
        'sender_id',
        'recipient_type',
        'recipient_id',
        'subject',
        'body',
        'message_type',
        'priority',
        'status',
        'sent_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'conversation_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class, 'message_id');
    }

    public function senderStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'sender_id');
    }

    public function senderGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'sender_id');
    }

    public function senderStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'sender_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
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

    public function recipientGroup(): BelongsTo
    {
        return $this->belongsTo(CommunicationGroup::class, 'recipient_id');
    }

    public function resolveSender()
    {
        return match ($this->sender_type) {
            'student' => $this->senderStudent,
            'guardian' => $this->senderGuardian,
            'staff' => $this->senderStaff,
            'user' => $this->senderUser,
            default => null,
        };
    }

    public function resolveRecipient()
    {
        return match ($this->recipient_type) {
            'student' => $this->recipientStudent,
            'guardian' => $this->recipientGuardian,
            'staff' => $this->recipientStaff,
            'user' => $this->recipientUser,
            'group' => $this->recipientGroup,
            default => null,
        };
    }
}
