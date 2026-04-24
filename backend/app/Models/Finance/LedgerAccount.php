<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerAccount extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_ledger_accounts';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'account_type',
        'parent_id',
        'status',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'ledger_account_id');
    }
}
