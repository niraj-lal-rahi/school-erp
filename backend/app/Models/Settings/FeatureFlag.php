<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureFlag extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'feature_flags';

    protected $fillable = [
        'school_id',
        'feature_code',
        'module',
        'name',
        'description',
        'is_enabled',
        'rollout_percentage',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'rollout_percentage' => 'integer',
            'config' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->is_enabled;
    }
}
