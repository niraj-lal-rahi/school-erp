<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantDomain extends Model
{
    use SoftDeletes;

    protected $table = 'tenant_domains';

    protected $fillable = [
        'school_id',
        'domain',
        'domain_type',
        'is_verified',
        'verified_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }
}
