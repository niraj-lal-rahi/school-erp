<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Concerns\OptimizesQueryFilters;
use App\Models\Saas\TenantSubscription;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use SoftDeletes;
    use BelongsToSchool;
    use OptimizesQueryFilters;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'school_id',
        'transaction_no',
        'payable_type',
        'payable_id',
        'student_id',
        'tenant_subscription_id',
        'gateway_id',
        'provider',
        'payment_method',
        'amount',
        'currency',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_signature',
        'upi_vpa',
        'upi_reference_no',
        'upi_qr_payload',
        'status',
        'verification_status',
        'paid_at',
        'verified_at',
        'verified_by',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function tenantSubscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class, 'transaction_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(PaymentReconciliation::class, 'transaction_id');
    }

    public function upiPaymentRequest(): HasOne
    {
        return $this->hasOne(UpiPaymentRequest::class, 'transaction_id');
    }
}
