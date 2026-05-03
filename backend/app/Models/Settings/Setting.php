<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'school_id',
        'group_id',
        'key',
        'value',
        'value_type',
        'scope',
        'is_sensitive',
        'is_public',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
            'is_public' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SettingGroup::class, 'group_id');
    }

    public function getTypedValue(): mixed
    {
        $value = $this->value;

        if ($value === null) {
            return null;
        }

        if ($this->value_type === 'encrypted' || $this->is_sensitive) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable) {
                // Keep the stored value if it predates encryption.
            }
        }

        return match ($this->value_type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'json' => is_array($value) ? $value : (json_decode($value, true) ?: []),
            default => $value,
        };
    }

    public function isPublic(): bool
    {
        return (bool) $this->is_public;
    }
}
