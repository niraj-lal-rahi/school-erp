<?php

namespace App\Models\Finance;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeInvoice extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'finance_fee_invoices';

    protected $fillable = [
        'school_id',
        'invoice_no',
        'student_id',
        'academic_year_id',
        'issue_date',
        'due_date',
        'subtotal',
        'discount_total',
        'fine_total',
        'tax_total',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'fine_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class, 'fee_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'fee_invoice_id');
    }
}
