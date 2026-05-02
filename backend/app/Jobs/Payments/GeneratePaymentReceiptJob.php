<?php

namespace App\Jobs\Payments;

use App\Models\Finance\Payment as FinancePayment;
use App\Repositories\Contracts\Payments\PaymentTransactionRepositoryInterface;
use App\Services\Finance\ReceiptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePaymentReceiptJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $transactionId,
    ) {
    }

    public function handle(
        PaymentTransactionRepositoryInterface $transactions,
        ReceiptService $receipts,
    ): void {
        $transaction = $transactions->findOrFail($this->transactionId);

        if ($transaction->payable_type !== 'school_fee') {
            return;
        }

        $payment = FinancePayment::withoutGlobalScopes()
            ->where('school_id', $transaction->school_id)
            ->where('gateway_transaction_id', $transaction->gateway_payment_id ?: $transaction->transaction_no)
            ->first();

        if (! $payment) {
            return;
        }

        $receipts->generateForPayment($payment);
    }
}
