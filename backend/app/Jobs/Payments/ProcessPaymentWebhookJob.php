<?php

namespace App\Jobs\Payments;

use App\Repositories\Contracts\Payments\PaymentWebhookRepositoryInterface;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $webhookEventId,
    ) {
    }

    public function handle(
        PaymentWebhookRepositoryInterface $webhooks,
        PaymentWebhookService $service,
    ): void {
        $event = $webhooks->findOrFail($this->webhookEventId);
        $service->processWebhook($event);
    }
}
