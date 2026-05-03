<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingAuditLog extends Model
{
    use BelongsToSchool;

    protected $table = 'setting_audit_logs';

    protected $fillable = [
        'school_id',
        'setting_type',
        'setting_key',
        'old_value',
        'new_value',
        'changed_by',
        'ip_address',
        'user_agent',
    ];

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
