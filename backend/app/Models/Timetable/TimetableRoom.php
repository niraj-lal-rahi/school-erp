<?php

namespace App\Models\Timetable;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableRoom extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'timetable_rooms';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'room_type',
        'capacity',
        'building',
        'floor',
        'status',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'room_id');
    }
}
