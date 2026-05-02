<?php

namespace App\Models\Saas;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAuditLog extends Model
{
    protected $table = 'tenant_audit_logs';

    protected $fillable = [
        'school_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'performed_by',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
