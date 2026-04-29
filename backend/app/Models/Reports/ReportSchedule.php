<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSchedule extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'report_schedules';

    protected $fillable = [
        'school_id',
        'report_definition_id',
        'schedule_type',
        'schedule_config',
        'next_run_at',
        'last_run_at',
        'channel',
        'recipients',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'schedule_config' => 'array',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
            'recipients' => 'array',
        ];
    }

    public function reportDefinition(): BelongsTo
    {
        return $this->belongsTo(ReportDefinition::class, 'report_definition_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
