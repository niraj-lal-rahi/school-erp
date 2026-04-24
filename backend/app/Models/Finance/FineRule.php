<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FineRule extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_fine_rules';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'fee_head_id',
        'fine_type',
        'amount',
        'grace_days',
        'max_fine_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'max_fine_amount' => 'decimal:2',
        ];
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }
}
