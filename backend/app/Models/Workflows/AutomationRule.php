<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomationRule extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'automation_rules';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'module',
        'trigger_type',
        'trigger_event',
        'schedule_expression',
        'conditions',
        'actions',
        'status',
        'last_run_at',
        'next_run_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions' => 'array',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class, 'automation_rule_id');
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(AutomationActionLog::class, 'automation_rule_id');
    }
}
