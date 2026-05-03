<?php

namespace App\Jobs\Payments;

use App\Repositories\Contracts\Payments\PaymentWebhookRepositoryInterface;
use App\Services\Payments\PaymentWebhookService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 4;
    public int $timeout = 120;

    public function __construct(
        public int $webhookEventId,
    ) {
        $this->onQueue(config('queue.routing.payments', QueueNames::PAYMENTS));
    }

    public function backoff(): array
    {
        return JobRetryProfile::payments();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('payment-webhook:'.$this->webhookEventId))
                ->releaseAfter(15)
                ->expireAfter(300),
        ];
    }

    public function handle(
        PaymentWebhookRepositoryInterface $webhooks,
        PaymentWebhookService $service,
    ): void {
        $event = $webhooks->findOrFail($this->webhookEventId);
        $service->processWebhook($event);
    }
}
