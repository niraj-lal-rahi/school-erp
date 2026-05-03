<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\FeeInvoiceData;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeInvoiceItem;
use App\Repositories\Contracts\Finance\FeeInvoiceRepositoryInterface;
use App\Support\Pagination\PaginationDefaults;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FeeInvoiceRepository implements FeeInvoiceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $invoiceQuery) use ($search): void {
                    $invoiceQuery->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['due_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('due_date', '>=', $value))
            ->when($filters['due_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('due_date', '<=', $value))
            ->latest('id')
            ->paginate(PaginationDefaults::resolvePerPage($perPage));
    }

    public function findOrFail(int $id): FeeInvoice
    {
        return $this->detailQuery()->findOrFail($id);
    }

    public function create(FeeInvoiceData $data, array $items): FeeInvoice
    {
        $invoice = FeeInvoice::create($data->attributes);

        $invoice->items()->createMany($items);

        return $this->findOrFail($invoice->id);
    }

    public function update(FeeInvoice $invoice, FeeInvoiceData $data, array $items): FeeInvoice
    {
        $invoice->update($data->attributes);
        $invoice->items()->delete();
        $invoice->items()->createMany($items);

        return $this->findOrFail($invoice->id);
    }

    public function delete(FeeInvoice $invoice): void
    {
        $invoice->delete();
    }

    public function nextInvoiceNumber(int $schoolId): string
    {
        $nextId = (int) FeeInvoice::withoutGlobalScopes()->where('school_id', $schoolId)->max('id') + 1;

        return 'INV-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    public function linkedInstallmentIds(array $installmentIds, ?int $ignoreInvoiceId = null): Collection
    {
        return FeeInvoiceItem::query()
            ->whereNotNull('fee_installment_id')
            ->whereIn('fee_installment_id', $installmentIds)
            ->whereHas('invoice', fn (Builder $query) => $query->where('status', '!=', 'cancelled'))
            ->when($ignoreInvoiceId, fn (Builder $query, int $value) => $query->where('fee_invoice_id', '!=', $value))
            ->pluck('fee_installment_id');
    }

    protected function baseQuery(): Builder
    {
        return FeeInvoice::query()->select([
            'finance_fee_invoices.id',
            'finance_fee_invoices.school_id',
            'finance_fee_invoices.invoice_no',
            'finance_fee_invoices.student_id',
            'finance_fee_invoices.academic_year_id',
            'finance_fee_invoices.issue_date',
            'finance_fee_invoices.due_date',
            'finance_fee_invoices.subtotal',
            'finance_fee_invoices.discount_total',
            'finance_fee_invoices.fine_total',
            'finance_fee_invoices.tax_total',
            'finance_fee_invoices.grand_total',
            'finance_fee_invoices.paid_amount',
            'finance_fee_invoices.balance_amount',
            'finance_fee_invoices.status',
            'finance_fee_invoices.notes',
            'finance_fee_invoices.created_by',
            'finance_fee_invoices.created_at',
            'finance_fee_invoices.updated_at',
        ]);
    }

    protected function listQuery(): Builder
    {
        return $this->baseQuery()->with([
            'student:id,full_name,admission_no',
            'academicYear:id,name,code',
            'creator:id,name,email',
        ]);
    }

    protected function detailQuery(): Builder
    {
        return $this->baseQuery()->with([
            'student',
            'academicYear',
            'creator',
            'items.feeHead',
            'items.installment',
        ]);
    }
}
