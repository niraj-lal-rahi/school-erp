<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationGroupMember extends Model
{
    use BelongsToSchool;

    protected $table = 'communication_group_members';

    protected $fillable = [
        'school_id',
        'group_id',
        'member_type',
        'member_id',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CommunicationGroup::class, 'group_id');
    }

    public function memberStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'member_id');
    }

    public function memberGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'member_id');
    }

    public function memberStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'member_id');
    }

    public function memberUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function resolveMember()
    {
        return match ($this->member_type) {
            'student' => $this->memberStudent,
            'guardian' => $this->memberGuardian,
            'staff' => $this->memberStaff,
            'user' => $this->memberUser,
            default => null,
        };
    }
}
