<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeInstallment extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_fee_installments';

    protected $fillable = [
        'school_id',
        'student_fee_assignment_id',
        'fee_head_id',
        'installment_name',
        'due_date',
        'amount',
        'discount_amount',
        'fine_amount',
        'paid_amount',
        'balance_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'fine_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function studentFeeAssignment(): BelongsTo
    {
        return $this->belongsTo(StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class, 'fee_installment_id');
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'fee_installment_id');
    }
}
