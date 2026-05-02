<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Requests\Payments\InitiateUpiPaymentRequest;
use App\Http\Requests\Payments\VerifyUpiPaymentRequest;
use App\Http\Resources\Payments\PaymentTransactionResource;
use App\Http\Resources\Payments\UpiPaymentRequestResource;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payments\UpiPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpiPaymentController extends PaymentController
{
    public function __construct(
        protected UpiPaymentService $upiPayments,
    ) {
    }

    public function initiate(InitiateUpiPaymentRequest $request): JsonResponse
    {
        $this->authorize('create', [PaymentTransaction::class, $request->validated()]);
        $result = $this->upiPayments->createUpiPaymentRequest($request->validated());

        return response()->json([
            'message' => 'UPI payment initiated successfully.',
            'data' => [
                'transaction' => new PaymentTransactionResource($result['transaction']),
                'upi_request' => new UpiPaymentRequestResource($result['upi_request']),
                'gateway_payload' => $result['gateway_payload'],
            ],
        ], 201);
    }

    public function verify(VerifyUpiPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $transaction = app(\App\Services\Payments\PaymentTransactionService::class)->findOrFail((int) $validated['transaction_id']);
        $this->authorize('verify', $transaction);
        $transaction = $this->upiPayments->verifyUpiReference(
            $transaction,
            $validated['upi_reference_no'],
            $validated,
        );

        return response()->json([
            'message' => 'UPI payment verification submitted successfully.',
            'data' => new PaymentTransactionResource($transaction),
        ]);
    }

    public function show(int $transactionId): JsonResponse
    {
        $upiRequest = $this->upiPayments->findByTransactionId($transactionId);
        abort_if(! $upiRequest, 404, 'UPI payment request not found.');
        $this->authorize('view', $upiRequest->transaction);

        return response()->json([
            'data' => new UpiPaymentRequestResource($upiRequest),
        ]);
    }

    public function manualVerify(Request $request, int $transactionId): JsonResponse
    {
        $validated = $request->validate([
            'upi_reference_no' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $upiRequest = $this->upiPayments->findByTransactionId($transactionId);
        abort_if(! $upiRequest, 404, 'UPI payment request not found.');
        $this->authorize('manualApprove', $upiRequest->transaction);

        $transaction = $this->upiPayments->manualVerify($upiRequest, $validated, $request->user());

        return response()->json([
            'message' => 'UPI payment manually verified successfully.',
            'data' => new PaymentTransactionResource($transaction),
        ]);
    }

    public function expire(int $transactionId): JsonResponse
    {
        $upiRequest = $this->upiPayments->findByTransactionId($transactionId);
        abort_if(! $upiRequest, 404, 'UPI payment request not found.');
        $this->authorize('cancel', $upiRequest->transaction);

        $upiRequest = $this->upiPayments->expireRequest($upiRequest);

        return response()->json([
            'message' => 'UPI payment request expired successfully.',
            'data' => new UpiPaymentRequestResource($upiRequest),
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        return response()->json([
            'data' => UpiPaymentRequestResource::collection($this->upiPayments->paginate(
                $request->only([
                    'school_id',
                    'provider',
                    'status',
                    'student_id',
                    'verification_status',
                    'date_from',
                    'date_to',
                ]),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }
}
