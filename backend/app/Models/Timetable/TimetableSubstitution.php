<?php

namespace App\Models\Timetable;

use App\Models\Concerns\BelongsToSchool;
use App\Models\HR\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableSubstitution extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'timetable_substitutions';

    protected $fillable = [
        'school_id',
        'timetable_entry_id',
        'original_staff_id',
        'substitute_staff_id',
        'substitution_date',
        'reason',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'substitution_date' => 'date',
    ];

    public function timetableEntry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class);
    }

    public function originalStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'original_staff_id');
    }

    public function substituteStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'substitute_staff_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
