<?php

namespace App\Jobs\Payments;

use App\Models\User;
use App\Repositories\Contracts\Payments\PaymentTransactionRepositoryInterface;
use App\Services\Payments\PaymentReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReconcilePaymentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $transactionId,
        public string $source,
        public string $newStatus,
        public ?int $performedById = null,
        public ?string $remarks = null,
        public array $metadata = [],
    ) {
    }

    public function handle(
        PaymentTransactionRepositoryInterface $transactions,
        PaymentReconciliationService $reconciliations,
    ): void {
        $transaction = $transactions->findOrFail($this->transactionId);
        $performedBy = $this->performedById
            ? User::query()->withoutGlobalScopes()->find($this->performedById)
            : null;

        $reconciliations->reconcile(
            $transaction,
            $this->source,
            $this->newStatus,
            $performedBy,
            $this->remarks,
            $this->metadata,
        );
    }
}
