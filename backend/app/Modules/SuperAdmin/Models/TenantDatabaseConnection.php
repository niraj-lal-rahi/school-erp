<?php

namespace App\Modules\SuperAdmin\Models;

use App\Modules\SuperAdmin\Models\Casts\EncryptedCredentialCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantDatabaseConnection extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'platform';

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

    protected $hidden = [
        'database_host',
        'database_port',
        'database_username',
        'database_password',
    ];

    protected function casts(): array
    {
        return [
            'database_host' => EncryptedCredentialCast::class,
            'database_port' => EncryptedCredentialCast::class,
            'database_username' => EncryptedCredentialCast::class,
            'database_password' => EncryptedCredentialCast::class,
            'is_active' => 'boolean',
            'last_connected_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }

    public function hasStoredPassword(): bool
    {
        return filled($this->getRawOriginal('database_password'));
    }

    public function hasStoredUsername(): bool
    {
        return filled($this->getRawOriginal('database_username'));
    }

    public function maskCredential(?string $value, int $visiblePrefix = 2, int $visibleSuffix = 2): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $stringValue = (string) $value;
        $length = mb_strlen($stringValue);

        if ($length <= ($visiblePrefix + $visibleSuffix)) {
            return str_repeat('*', $length);
        }

        return mb_substr($stringValue, 0, $visiblePrefix)
            .str_repeat('*', max(4, $length - ($visiblePrefix + $visibleSuffix)))
            .mb_substr($stringValue, -$visibleSuffix);
    }
}
