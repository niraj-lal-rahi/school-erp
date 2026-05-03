<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRequest extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'approval_requests';

    protected $fillable = [
        'school_id',
        'workflow_instance_id',
        'module',
        'reference_type',
        'reference_id',
        'requested_by',
        'approver_id',
        'approver_role_id',
        'status',
        'requested_at',
        'responded_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approverRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_role_id');
    }
}
