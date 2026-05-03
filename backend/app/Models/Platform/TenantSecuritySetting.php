<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSecuritySetting extends Model
{
    protected $connection = 'platform';

    protected $table = 'tenant_security_settings';

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
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }

    public function isEnabled(): bool
    {
        return $this->status === 'active';
    }
}
