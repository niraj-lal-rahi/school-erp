<?php

namespace App\Models\Timetable;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetablePublishLog extends Model
{
    use BelongsToSchool;

    protected $table = 'timetable_publish_logs';

    protected $fillable = [
        'school_id',
        'timetable_version_id',
        'action',
        'performed_by',
        'remarks',
    ];

    public function timetableVersion(): BelongsTo
    {
        return $this->belongsTo(TimetableVersion::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
