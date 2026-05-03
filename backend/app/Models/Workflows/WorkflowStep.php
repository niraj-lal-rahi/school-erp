<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStep extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'workflow_steps';

    protected $fillable = [
        'school_id',
        'workflow_definition_id',
        'step_name',
        'step_type',
        'sequence',
        'config',
        'assigned_role_id',
        'assigned_user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'sequence' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function workflowDefinition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function stepInstances(): HasMany
    {
        return $this->hasMany(WorkflowStepInstance::class, 'workflow_step_id');
    }
}
