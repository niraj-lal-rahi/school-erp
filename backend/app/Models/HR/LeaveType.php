<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'annual_quota',
        'carry_forward_allowed',
        'paid_leave',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'annual_quota' => 'decimal:2',
            'carry_forward_allowed' => 'bool',
            'paid_leave' => 'bool',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StaffLeaveApplication::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
