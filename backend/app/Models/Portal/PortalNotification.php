<?php

namespace App\Models\Portal;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortalNotification extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id',
        'student_id',
        'title',
        'message',
        'notification_type',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'bool',
            'read_at' => 'datetime',
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
}
