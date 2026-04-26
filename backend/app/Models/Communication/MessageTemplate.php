<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'message_templates';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'template_type',
        'subject',
        'body',
        'variables',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'template_id');
    }

    public function scheduledMessages(): HasMany
    {
        return $this->hasMany(ScheduledMessage::class, 'template_id');
    }
}
