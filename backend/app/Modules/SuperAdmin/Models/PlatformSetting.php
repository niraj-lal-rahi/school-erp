<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformSetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'platform';

    protected $fillable = [
        'setting_group',
        'key',
        'value',
        'value_type',
        'is_sensitive',
        'is_public',
        'description',
        'metadata',
    ];

    protected $hidden = [
        'value',
    ];

    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
            'is_public' => 'boolean',
            'metadata' => 'array',
            'deleted_at' => 'datetime',
        ];
    }
}
