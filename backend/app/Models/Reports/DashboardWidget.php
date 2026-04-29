<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DashboardWidget extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'school_id',
        'name',
        'widget_type',
        'module',
        'config',
        'position',
        'is_system',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'position' => 'array',
            'is_system' => 'boolean',
        ];
    }
}
