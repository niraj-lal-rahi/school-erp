<?php

namespace App\Http\Requests\Payments;

use Illuminate\Validation\Rule;

class WebhookRequest extends PaymentRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $provider = $this->route('provider');

        if (! $provider) {
            $path = (string) $this->path();

            if (str_contains($path, 'payments/webhooks/razorpay')) {
                $provider = 'razorpay';
            } elseif (str_contains($path, 'payments/webhooks/stripe')) {
                $provider = 'stripe';
            }
        }

        if ($provider && ! $this->has('provider')) {
            $this->merge(['provider' => $provider]);
        }
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', Rule::in(['razorpay', 'stripe'])],
            'event_type' => ['nullable', 'string', 'max:255'],
            'event_id' => ['nullable', 'string', 'max:255'],
            'signature' => ['nullable', 'string'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
