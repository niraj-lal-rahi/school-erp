<?php

namespace App\Listeners\Finance;

use App\Events\Finance\InvoicePaid;
use Illuminate\Support\Facades\Log;

class LogInvoicePaid
{
    public function handle(InvoicePaid $event): void
    {
        Log::info('Finance invoice fully paid.', [
            'invoice_id' => $event->invoice->id,
            'invoice_no' => $event->invoice->invoice_no,
            'school_id' => $event->invoice->school_id,
        ]);
    }
}
