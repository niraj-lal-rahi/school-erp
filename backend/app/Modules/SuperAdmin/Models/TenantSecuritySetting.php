<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSecuritySetting extends Model
{
    use HasFactory;

    protected $connection = 'platform';

    protected $fillable = [
        'tenant_id',
        'encryption_enabled',
        'database_isolated',
        'emergency_access_enabled',
        'backup_encryption_enabled',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'encryption_enabled' => 'boolean',
            'database_isolated' => 'boolean',
            'emergency_access_enabled' => 'boolean',
            'backup_encryption_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }
}
