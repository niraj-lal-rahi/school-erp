<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffPayslip extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'payroll_run_id',
        'staff_id',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'earnings_breakdown',
        'deductions_breakdown',
        'payment_status',
        'paid_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'earnings_breakdown' => 'array',
            'deductions_breakdown' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
