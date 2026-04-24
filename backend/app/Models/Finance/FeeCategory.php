<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCategory extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_fee_categories';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function feeHeads(): HasMany
    {
        return $this->hasMany(FeeHead::class, 'fee_category_id');
    }
}
