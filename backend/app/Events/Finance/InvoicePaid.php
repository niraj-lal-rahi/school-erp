<?php

namespace App\Events\Finance;

use App\Models\Finance\FeeInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public FeeInvoice $invoice,
    ) {
    }
}
