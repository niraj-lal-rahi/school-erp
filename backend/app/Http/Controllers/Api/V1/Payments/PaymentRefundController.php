<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Requests\Payments\RefundPaymentRequest;
use App\Http\Resources\Payments\PaymentRefundResource;
use App\Models\Payments\PaymentRefund;
use App\Services\Payments\PaymentRefundService;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentRefundController extends PaymentController
{
    public function __construct(
        protected PaymentRefundService $refunds,
        protected PaymentTransactionService $transactions,
    ) {
    }

    public function store(RefundPaymentRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $transaction = $this->transactions->findOrFail($id);
        $this->authorize('create', [PaymentRefund::class, $transaction]);
        $refund = $this->refunds->requestRefund($transaction, $validated, $request->user());

        return response()->json([
            'message' => 'Refund requested successfully.',
            'data' => new PaymentRefundResource($refund),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentRefund::class);

        return response()->json([
            'data' => PaymentRefundResource::collection($this->refunds->paginate(
                $request->only([
                    'school_id',
                    'provider',
                    'payment_method',
                    'status',
                    'student_id',
                    'payable_type',
                    'date_from',
                    'date_to',
                ]),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function process(int $id): JsonResponse
    {
        $refund = $this->refunds->findOrFail($id);
        $this->authorize('process', $refund);
        $refund = $this->refunds->processRefund($refund);

        return response()->json([
            'message' => 'Refund processed successfully.',
            'data' => new PaymentRefundResource($refund),
        ]);
    }
}
