<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayCredential extends Model
{
    use BelongsToSchool;

    protected $table = 'payment_gateway_credentials';

    protected $fillable = [
        'school_id',
        'payment_gateway_id',
        'key_name',
        'key_value',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function getKeyValueAttribute(?string $value): ?string
    {
        if ($value === null || ! $this->is_encrypted) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function setKeyValueAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['key_value'] = null;

            return;
        }

        $this->attributes['key_value'] = ($this->attributes['is_encrypted'] ?? true)
            ? Crypt::encryptString($value)
            : $value;
    }
}
