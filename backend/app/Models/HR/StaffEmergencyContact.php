<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffEmergencyContact extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'staff_id',
        'contact_name',
        'relationship',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'bool',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
