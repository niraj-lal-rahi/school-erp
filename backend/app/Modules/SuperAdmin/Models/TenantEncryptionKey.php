<?php

namespace App\Modules\SuperAdmin\Models;

use App\Modules\SuperAdmin\Models\Casts\EncryptedCredentialCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantEncryptionKey extends Model
{
    use HasFactory;

    protected $connection = 'platform';

    protected $fillable = [
        'tenant_id',
        'key_reference',
        'encrypted_data_key',
        'key_version',
        'status',
        'activated_at',
        'rotated_at',
    ];

    protected $hidden = [
        'encrypted_data_key',
    ];

    protected function casts(): array
    {
        return [
            'encrypted_data_key' => EncryptedCredentialCast::class,
            'key_version' => 'integer',
            'activated_at' => 'datetime',
            'rotated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }
}
