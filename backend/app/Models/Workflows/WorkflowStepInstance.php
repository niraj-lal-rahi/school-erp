<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStepInstance extends Model
{
    use BelongsToSchool;

    protected $table = 'workflow_step_instances';

    protected $fillable = [
        'school_id',
        'workflow_instance_id',
        'workflow_step_id',
        'assigned_to',
        'status',
        'action_taken_by',
        'action_taken_at',
        'remarks',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'action_taken_at' => 'datetime',
        ];
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_taken_by');
    }
}
