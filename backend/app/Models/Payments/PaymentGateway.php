<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model
{
    use SoftDeletes;
    use BelongsToSchool;

    protected $table = 'payment_gateways';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'provider',
        'mode',
        'config',
        'supports_upi',
        'supports_card',
        'supports_netbanking',
        'supports_wallet',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'supports_upi' => 'boolean',
            'supports_card' => 'boolean',
            'supports_netbanking' => 'boolean',
            'supports_wallet' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(PaymentGatewayCredential::class, 'payment_gateway_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'gateway_id');
    }
}
