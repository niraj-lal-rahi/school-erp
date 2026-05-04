<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemHealthLog extends Model
{
    use HasFactory;

    protected $connection = 'platform';

    protected $fillable = [
        'tenant_id',
        'component',
        'check_name',
        'status',
        'response_time_ms',
        'message',
        'metadata',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'response_time_ms' => 'integer',
            'metadata' => 'array',
            'checked_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }
}
