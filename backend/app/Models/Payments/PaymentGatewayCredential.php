<?php

namespace App\Models\Payments;

use App\Modules\Tenant\Casts\EncryptedStringCast;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'key_value' => EncryptedStringCast::class,
        ];
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}
