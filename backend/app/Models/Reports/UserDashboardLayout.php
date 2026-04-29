<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class UserDashboardLayout extends Model
{
    use BelongsToSchool;

    protected $table = 'user_dashboard_layouts';

    protected $fillable = [
        'school_id',
        'user_type',
        'user_id',
        'layout',
    ];

    protected function casts(): array
    {
        return [
            'layout' => 'array',
        ];
    }
}
