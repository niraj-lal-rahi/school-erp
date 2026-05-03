<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationActionLog extends Model
{
    use BelongsToSchool;

    protected $table = 'automation_action_logs';

    protected $fillable = [
        'school_id',
        'automation_run_id',
        'automation_rule_id',
        'action_type',
        'reference_type',
        'reference_id',
        'status',
        'payload',
        'response',
        'error_message',
        'executed_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'payload' => 'array',
            'response' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function automationRun(): BelongsTo
    {
        return $this->belongsTo(AutomationRun::class, 'automation_run_id');
    }

    public function automationRule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }
}
