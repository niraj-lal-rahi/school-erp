<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Http\Requests\Payments\ManualPaymentApprovalRequest;
use App\Http\Requests\Payments\VerifyPaymentRequest;
use App\Http\Resources\Payments\PaymentGatewayResource;
use App\Http\Resources\Payments\PaymentTransactionResource;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTransactionController extends PaymentController
{
    public function __construct(
        protected PaymentTransactionService $transactions,
    ) {
    }

    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', [PaymentTransaction::class, $request->validated()]);
        $result = $this->transactions->initiatePayment($request->validated());

        return response()->json([
            'message' => 'Payment initiated successfully.',
            'data' => [
                'transaction' => new PaymentTransactionResource($result['transaction']),
                'gateway' => new PaymentGatewayResource($result['gateway']),
                'payload' => $result['payload'],
            ],
        ], 201);
    }

    public function verify(VerifyPaymentRequest $request): JsonResponse
    {
        $transaction = $this->transactions->findOrFail((int) $request->validated('transaction_id'));
        $this->authorize('verify', $transaction);
        $transaction = $this->transactions->verifyPayment($transaction, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Payment verified successfully.',
            'data' => new PaymentTransactionResource($transaction),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        return response()->json([
            'data' => PaymentTransactionResource::collection($this->transactions->paginate(
                $request->only([
                    'school_id',
                    'provider',
                    'payment_method',
                    'status',
                    'verification_status',
                    'student_id',
                    'payable_type',
                    'date_from',
                    'date_to',
                ]),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->transactions->findOrFail($id);
        $this->authorize('view', $transaction);

        return response()->json([
            'data' => new PaymentTransactionResource($transaction),
        ]);
    }

    public function manualApprove(ManualPaymentApprovalRequest $request, int $id): JsonResponse
    {
        $transaction = $this->transactions->findOrFail($id);
        $this->authorize('manualApprove', $transaction);
        $transaction = $this->transactions->manualApprove($transaction, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Payment approval updated successfully.',
            'data' => new PaymentTransactionResource($transaction),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        $transaction = $this->transactions->findOrFail($id);
        $this->authorize('cancel', $transaction);
        $transaction = $this->transactions->cancel($transaction, $request->user(), $request->input('reason'));

        return response()->json([
            'message' => 'Payment cancelled successfully.',
            'data' => new PaymentTransactionResource($transaction),
        ]);
    }

    public function paymentSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        return response()->json([
            'data' => $this->transactions->paymentSummary(
                $request->only([
                    'school_id',
                    'provider',
                    'payment_method',
                    'status',
                    'verification_status',
                    'student_id',
                    'payable_type',
                    'date_from',
                    'date_to',
                ]),
            ),
        ]);
    }

    public function failedTransactions(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        $filters = array_merge($request->only([
            'school_id',
            'provider',
            'payment_method',
            'verification_status',
            'student_id',
            'payable_type',
            'date_from',
            'date_to',
        ]), ['status' => 'failed']);

        return response()->json([
            'data' => $this->transactions->paginate($filters, (int) $request->integer('per_page', 15)),
        ]);
    }
}
