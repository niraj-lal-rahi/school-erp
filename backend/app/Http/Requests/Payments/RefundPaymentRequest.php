<?php

namespace App\Http\Requests\Payments;

use App\Models\Payments\PaymentTransaction;
use Illuminate\Validation\Rule;

class RefundPaymentRequest extends PaymentRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (! $this->has('transaction_id')) {
            $transactionId = $this->routeModelId('id') ?? $this->routeModelId('transaction');

            if ($transactionId) {
                $this->merge(['transaction_id' => $transactionId]);
            }
        }
    }

    public function rules(): array
    {
        $schoolId = $this->integer('school_id') ?: null;

        return [
            'school_id' => ['required', $this->schoolExists()],
            'transaction_id' => ['required', $this->transactionExists($schoolId)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['nullable', 'string'],
            'requested_by' => ['nullable', $this->userExists($schoolId)],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $transactionId = $this->integer('transaction_id');

            if (! $transactionId) {
                return;
            }

            $transaction = PaymentTransaction::query()
                ->with('refunds')
                ->find($transactionId);

            if (! $transaction) {
                return;
            }

            $paidAmount = (float) $transaction->amount;
            $alreadyRefunded = (float) $transaction->refunds
                ->whereIn('status', ['requested', 'processing', 'successful'])
                ->sum('amount');
            $remaining = $paidAmount - $alreadyRefunded;

            if ((float) $this->input('amount', 0) > $remaining) {
                $validator->errors()->add('amount', 'Refund amount cannot be greater than the remaining paid amount.');
            }
        });
    }
}
