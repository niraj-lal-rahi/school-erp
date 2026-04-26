<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use BelongsToSchool;

    protected $table = 'conversation_participants';

    protected $fillable = [
        'school_id',
        'conversation_id',
        'participant_type',
        'participant_id',
        'joined_at',
        'left_at',
        'is_muted',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'is_muted' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'conversation_id');
    }

    public function participantStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'participant_id');
    }

    public function participantGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'participant_id');
    }

    public function participantStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'participant_id');
    }

    public function participantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function resolveParticipant()
    {
        return match ($this->participant_type) {
            'student' => $this->participantStudent,
            'guardian' => $this->participantGuardian,
            'staff' => $this->participantStaff,
            'user' => $this->participantUser,
            default => null,
        };
    }
}
