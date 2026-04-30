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

class PortalProfileAccess extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'portal_profile_access';

    protected $fillable = [
        'school_id',
        'user_id',
        'student_id',
        'guardian_id',
        'access_type',
        'can_view_attendance',
        'can_view_fees',
        'can_pay_fees',
        'can_view_results',
        'can_view_documents',
        'can_message_teacher',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'can_view_attendance' => 'bool',
            'can_view_fees' => 'bool',
            'can_pay_fees' => 'bool',
            'can_view_results' => 'bool',
            'can_view_documents' => 'bool',
            'can_message_teacher' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }
}
