<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAdmin extends Model
{
    protected $connection = 'platform';

    protected $table = 'platform_admins';

    protected $fillable = [
        'user_id',
        'admin_type',
        'status',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
