<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPermission extends Model
{
    use BelongsToSchool;

    protected $table = 'document_permissions';

    protected $fillable = [
        'school_id',
        'document_id',
        'permission_type',
        'permission_id',
        'can_view',
        'can_download',
        'can_update',
        'can_delete',
        'can_verify',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_download' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
            'can_verify' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'permission_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'permission_id');
    }
}
