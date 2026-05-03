<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class TenantDatabaseConnection extends Model
{
    use SoftDeletes;

    protected $connection = 'platform';

    protected $table = 'tenant_database_connections';

    protected $fillable = [
        'tenant_id',
        'connection_name',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'database_driver',
        'is_active',
        'last_connected_at',
        'connection_status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_connected_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }

    public function getDatabaseHostAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    public function setDatabaseHostAttribute(?string $value): void
    {
        $this->attributes['database_host'] = $this->encryptValue($value);
    }

    public function getDatabasePortAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    public function setDatabasePortAttribute(?string $value): void
    {
        $this->attributes['database_port'] = $this->encryptValue($value);
    }

    public function getDatabaseUsernameAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    public function setDatabaseUsernameAttribute(?string $value): void
    {
        $this->attributes['database_username'] = $this->encryptValue($value);
    }

    public function getDatabasePasswordAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    public function setDatabasePasswordAttribute(?string $value): void
    {
        $this->attributes['database_password'] = $this->encryptValue($value);
    }

    protected function decryptValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function encryptValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return Crypt::encryptString($value);
    }
}
