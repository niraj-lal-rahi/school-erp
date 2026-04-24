<?php

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_refunds';

    protected $fillable = [
        'school_id',
        'refund_no',
        'payment_id',
        'student_id',
        'refund_date',
        'amount',
        'reason',
        'status',
        'approved_by',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'refund_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
