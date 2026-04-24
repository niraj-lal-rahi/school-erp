<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffStatusHistory extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'staff_id',
        'previous_status',
        'new_status',
        'action_type',
        'reason',
        'effective_date',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
