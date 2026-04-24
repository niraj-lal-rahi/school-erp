<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeHead extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_fee_heads';

    protected $fillable = [
        'school_id',
        'fee_category_id',
        'name',
        'code',
        'amount_type',
        'default_amount',
        'is_refundable',
        'is_optional',
        'status',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_refundable' => 'boolean',
        'is_optional' => 'boolean',
    ];

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }

    public function structureItems(): HasMany
    {
        return $this->hasMany(FeeStructureItem::class, 'fee_head_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class, 'fee_head_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class, 'fee_head_id');
    }
}
