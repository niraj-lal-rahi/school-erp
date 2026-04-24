<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_payments';

    protected $fillable = [
        'school_id',
        'payment_no',
        'student_id',
        'fee_invoice_id',
        'payment_date',
        'payment_method',
        'gateway_provider',
        'gateway_transaction_id',
        'reference_no',
        'amount',
        'status',
        'received_by',
        'remarks',
        'confirmed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class, 'payment_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }
}
