<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportDefinition extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'report_definitions';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'module',
        'description',
        'query_config',
        'default_filters',
        'is_system',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'query_config' => 'array',
            'default_filters' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class, 'report_definition_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ReportRun::class, 'report_definition_id');
    }
}
