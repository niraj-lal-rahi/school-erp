<?php

namespace App\Models\Settings;

use App\Modules\Tenant\Casts\EncryptedArrayCast;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegrationSetting extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'integration_settings';

    protected $fillable = [
        'school_id',
        'integration_type',
        'provider',
        'config',
        'encrypted_config',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'encrypted_config' => EncryptedArrayCast::class,
            'deleted_at' => 'datetime',
        ];
    }

    public function getTypedValue(): mixed
    {
        return $this->safeConfig();
    }

    public function isEnabled(): bool
    {
        return $this->status === 'active';
    }

    public function safeConfig(): array
    {
        return $this->config ?? [];
    }

    public function decryptedSecretConfig(): array
    {
        return $this->encrypted_config ?? [];
    }
}
