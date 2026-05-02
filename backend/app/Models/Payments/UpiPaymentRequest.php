<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpiPaymentRequest extends Model
{
    use SoftDeletes;
    use BelongsToSchool;

    protected $table = 'upi_payment_requests';

    protected $fillable = [
        'school_id',
        'transaction_id',
        'upi_vpa',
        'payee_name',
        'amount',
        'currency',
        'qr_payload',
        'qr_image_path',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }
}
