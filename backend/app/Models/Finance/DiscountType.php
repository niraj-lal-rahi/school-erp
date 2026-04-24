<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountType extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_discount_types';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'discount_type',
        'value',
        'max_amount',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_amount' => 'decimal:2',
        ];
    }

    public function studentDiscounts(): HasMany
    {
        return $this->hasMany(StudentDiscount::class, 'discount_type_id');
    }
}
