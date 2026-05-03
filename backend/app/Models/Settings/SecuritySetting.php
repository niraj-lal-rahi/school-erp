<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use BelongsToSchool;

    protected $table = 'security_settings';

    protected $fillable = [
        'school_id',
        'password_min_length',
        'password_requires_uppercase',
        'password_requires_number',
        'password_requires_symbol',
        'session_timeout_minutes',
        'max_login_attempts',
        'lockout_minutes',
        'two_factor_enabled',
    ];

    protected function casts(): array
    {
        return [
            'password_min_length' => 'integer',
            'password_requires_uppercase' => 'boolean',
            'password_requires_number' => 'boolean',
            'password_requires_symbol' => 'boolean',
            'session_timeout_minutes' => 'integer',
            'max_login_attempts' => 'integer',
            'lockout_minutes' => 'integer',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->two_factor_enabled;
    }
}
