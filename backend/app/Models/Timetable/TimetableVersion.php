<?php

namespace App\Models\Timetable;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableVersion extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'timetable_versions';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'code',
        'effective_from',
        'effective_to',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'published_at' => 'datetime',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publishLogs(): HasMany
    {
        return $this->hasMany(TimetablePublishLog::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function substitutions(): HasManyThrough
    {
        return $this->hasManyThrough(
            TimetableSubstitution::class,
            TimetableEntry::class,
            'timetable_version_id',
            'timetable_entry_id'
        );
    }
}
