<?php

namespace App\Events\Finance;

use App\Models\Finance\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiptGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Receipt $receipt,
    ) {
    }
}
