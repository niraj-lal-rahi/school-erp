<?php

namespace App\Models\Workflows;

use App\Models\Communication\MessageTemplate;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReminderRule extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'reminder_rules';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'module',
        'reminder_type',
        'offset_days',
        'frequency',
        'channel',
        'template_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'offset_days' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ReminderLog::class, 'reminder_rule_id');
    }
}
