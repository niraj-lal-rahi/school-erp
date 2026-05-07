<?php

namespace App\Modules\SuperAdmin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantKeyRotationLog extends Model
{
    use HasFactory;

    protected $connection = 'platform';

    protected $fillable = [
        'tenant_id',
        'old_key_version',
        'new_key_version',
        'rotated_by',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'old_key_version' => 'integer',
            'new_key_version' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }

    public function rotatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rotated_by');
    }
}
