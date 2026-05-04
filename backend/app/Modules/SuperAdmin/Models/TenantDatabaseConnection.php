<?php

namespace App\Modules\SuperAdmin\Models;

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
            'database_host' => 'encrypted',
            'database_port' => 'encrypted',
            'database_username' => 'encrypted',
            'database_password' => 'encrypted',
            'is_active' => 'boolean',
            'last_connected_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
    }
}
