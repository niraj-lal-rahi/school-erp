<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    use BelongsToSchool;

    protected $table = 'reminder_logs';

    protected $fillable = [
        'school_id',
        'reminder_rule_id',
        'recipient_type',
        'recipient_id',
        'reference_type',
        'reference_id',
        'channel',
        'status',
        'sent_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'recipient_id' => 'integer',
            'reference_id' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function reminderRule(): BelongsTo
    {
        return $this->belongsTo(ReminderRule::class, 'reminder_rule_id');
    }
}
