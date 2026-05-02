<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBillingRecord extends Model
{
    protected $table = 'tenant_billing_records';

    protected $fillable = [
        'school_id',
        'subscription_id',
        'invoice_no',
        'amount',
        'currency',
        'billing_cycle',
        'billing_date',
        'due_date',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }
}
