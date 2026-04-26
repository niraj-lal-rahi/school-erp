<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledMessage extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'scheduled_messages';

    protected $fillable = [
        'school_id',
        'template_id',
        'title',
        'message',
        'audience_type',
        'class_id',
        'section_id',
        'channel',
        'scheduled_at',
        'status',
        'created_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
