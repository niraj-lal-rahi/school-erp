<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

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
        if (blank($this->encrypted_config)) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($this->encrypted_config), true) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }
}
