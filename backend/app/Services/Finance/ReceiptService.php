<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\ReceiptData;
use App\Events\Finance\ReceiptGenerated;
use App\Models\Finance\Payment;
use App\Models\Finance\Receipt;
use App\Repositories\Contracts\Finance\ReceiptRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReceiptService
{
    public function __construct(
        protected ReceiptRepositoryInterface $receipts,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->receipts->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Receipt
    {
        return $this->receipts->findOrFail($id);
    }

    public function generateForPayment(Payment $payment): Receipt
    {
        $existing = $this->receipts->forPayment($payment);
        if ($existing) {
            return $existing;
        }

        $receipt = DB::transaction(fn (): Receipt => $this->receipts->create(ReceiptData::fromArray([
            'school_id' => $payment->school_id,
            'receipt_no' => $this->receipts->nextReceiptNumber($payment->school_id),
            'payment_id' => $payment->id,
            'student_id' => $payment->student_id,
            'receipt_date' => $payment->payment_date,
            'amount' => $payment->amount,
            'receipt_pdf_path' => null,
            'issued_by' => $payment->received_by,
        ])));

        event(new ReceiptGenerated($receipt));

        return $receipt;
    }
}
