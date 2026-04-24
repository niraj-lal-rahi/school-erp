<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    use BelongsToSchool;

    protected $table = 'finance_payment_allocations';

    protected $fillable = [
        'school_id',
        'payment_id',
        'fee_invoice_id',
        'fee_invoice_item_id',
        'fee_installment_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(FeeInvoiceItem::class, 'fee_invoice_item_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(FeeInstallment::class, 'fee_installment_id');
    }
}
