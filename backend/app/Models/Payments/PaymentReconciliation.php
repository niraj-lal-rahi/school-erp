<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReconciliation extends Model
{
    use BelongsToSchool;

    protected $table = 'payment_reconciliations';

    protected $fillable = [
        'school_id',
        'transaction_id',
        'source',
        'old_status',
        'new_status',
        'reconciled_by',
        'reconciled_at',
        'remarks',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'reconciled_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
