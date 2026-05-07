<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class PlatformSetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'platform';

    protected $fillable = [
        'setting_group',
        'key',
        'value',
        'value_type',
        'is_sensitive',
        'is_public',
        'description',
        'metadata',
    ];

    protected $hidden = [
        'value',
    ];

    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
            'is_public' => 'boolean',
            'metadata' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    public function typedValue(): mixed
    {
        $value = $this->is_sensitive ? $this->decryptedValue() : $this->value;

        if ($value === null) {
            return null;
        }

        return match ($this->value_type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'integer' => (int) $value,
            'json', 'file' => is_array($value) ? $value : json_decode((string) $value, true),
            default => $value,
        };
    }

    public function decryptedValue(): mixed
    {
        if (! $this->is_sensitive || blank($this->value)) {
            return $this->value;
        }

        try {
            return Crypt::decryptString((string) $this->value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function maskedValue(): ?string
    {
        if (! $this->is_sensitive) {
            return null;
        }

        if (blank($this->value)) {
            return null;
        }

        return '********';
    }
}
