<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFeatureAccess extends Model
{
    protected $table = 'tenant_feature_access';

    protected $fillable = [
        'school_id',
        'feature_code',
        'module',
        'is_enabled',
        'limit_value',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }
}
