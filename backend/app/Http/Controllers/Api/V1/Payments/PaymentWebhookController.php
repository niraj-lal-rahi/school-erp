<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Requests\Payments\WebhookRequest;
use App\Http\Resources\Payments\PaymentWebhookEventResource;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends PaymentController
{
    public function __construct(
        protected PaymentWebhookService $webhooks,
    ) {
    }

    public function razorpay(WebhookRequest $request): JsonResponse
    {
        return $this->storeWebhook('razorpay', $request);
    }

    public function stripe(WebhookRequest $request): JsonResponse
    {
        return $this->storeWebhook('stripe', $request);
    }

    protected function storeWebhook(string $provider, Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);

        if (! is_array($payload) || $payload === []) {
            $payload = $request->all();
        }

        if (
            isset($payload['payload'])
            && is_array($payload['payload'])
            && ! isset($payload['id'], $payload['event'], $payload['type'], $payload['event_id'])
        ) {
            $payload = $payload['payload'];
        }

        $signature = $request->header('X-Razorpay-Signature')
            ?: $request->header('Stripe-Signature')
            ?: $request->input('signature');

        $event = $this->webhooks->receiveWebhook($provider, $payload, $signature);

        return response()->json([
            'message' => 'Webhook received successfully.',
            'data' => new PaymentWebhookEventResource($event),
        ], 202);
    }
}
