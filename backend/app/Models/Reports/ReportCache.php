<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class ReportCache extends Model
{
    use BelongsToSchool;

    protected $table = 'report_cache';

    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'cache_key',
        'data',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
