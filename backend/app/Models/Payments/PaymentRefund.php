<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRefund extends Model
{
    use SoftDeletes;
    use BelongsToSchool;

    protected $table = 'payment_refunds';

    protected $fillable = [
        'school_id',
        'transaction_id',
        'refund_no',
        'gateway_refund_id',
        'amount',
        'reason',
        'status',
        'requested_by',
        'processed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'processed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
