<?php

namespace App\Models\HR;

use App\Modules\Tenant\Casts\EncryptedStringCast;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffBankDetail extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'staff_id',
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'branch_name',
        'account_type',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'bool',
            'account_number' => EncryptedStringCast::class,
            'ifsc_code' => EncryptedStringCast::class,
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
