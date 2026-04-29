<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportRun extends Model
{
    use BelongsToSchool;

    protected $table = 'report_runs';

    protected $fillable = [
        'school_id',
        'report_definition_id',
        'run_type',
        'status',
        'started_at',
        'completed_at',
        'file_path',
        'file_type',
        'parameters',
        'error_message',
        'initiated_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'parameters' => 'array',
        ];
    }

    public function reportDefinition(): BelongsTo
    {
        return $this->belongsTo(ReportDefinition::class, 'report_definition_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class, 'report_run_id');
    }
}
