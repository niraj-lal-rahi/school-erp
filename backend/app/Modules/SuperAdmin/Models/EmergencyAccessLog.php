<?php

namespace App\Modules\SuperAdmin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyAccessLog extends Model
{
    use HasFactory;

    protected $connection = 'platform';

    protected $fillable = [
        'tenant_id',
        'platform_admin_user_id',
        'approved_by_user_id',
        'action',
        'reason',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }

    public function platformAdminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'platform_admin_user_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
