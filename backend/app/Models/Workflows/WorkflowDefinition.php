<?php

namespace App\Models\Workflows;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowDefinition extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'workflow_definitions';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'module',
        'description',
        'trigger_type',
        'trigger_event',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_definition_id')->orderBy('sequence');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'workflow_definition_id');
    }
}
