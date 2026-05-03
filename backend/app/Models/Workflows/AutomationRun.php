<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRun extends Model
{
    use BelongsToSchool;

    protected $table = 'automation_runs';

    protected $fillable = [
        'school_id',
        'automation_rule_id',
        'status',
        'started_at',
        'completed_at',
        'records_processed',
        'success_count',
        'failed_count',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'records_processed' => 'integer',
            'success_count' => 'integer',
            'failed_count' => 'integer',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function automationRule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(AutomationActionLog::class, 'automation_run_id');
    }
}
