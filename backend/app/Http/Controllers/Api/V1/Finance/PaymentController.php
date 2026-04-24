<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\PaymentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CollectPaymentRequest;
use App\Http\Requests\Finance\PaymentDecisionRequest;
use App\Http\Requests\Finance\UpdatePaymentRequest;
use App\Http\Resources\Finance\PaymentResource;
use App\Models\Finance\Payment;
use App\Services\Finance\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        return response()->json(
            PaymentResource::collection($this->payments->paginate(
                $request->only(['search', 'student_id', 'fee_invoice_id', 'status', 'payment_date_from', 'payment_date_to']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(CollectPaymentRequest $request): JsonResponse
    {
        return $this->collect($request);
    }

    public function collect(CollectPaymentRequest $request): JsonResponse
    {
        $payment = $this->payments->collect(PaymentData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'received_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Payment collected successfully.',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return response()->json([
            'data' => new PaymentResource($payment->load(['student', 'invoice', 'receiver', 'receipt', 'allocations.invoice', 'allocations.installment'])),
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment = $this->payments->update($payment, $request->validated());

        return response()->json([
            'message' => 'Payment updated successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);
        $this->payments->delete($payment);

        return response()->json(null, 204);
    }

    public function confirm(Payment $payment, PaymentDecisionRequest $request): JsonResponse
    {
        $payment = $this->payments->confirm($payment, $request->validated());

        return response()->json([
            'message' => 'Payment confirmed successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }

    public function fail(Payment $payment, PaymentDecisionRequest $request): JsonResponse
    {
        $payment = $this->payments->fail($payment, $request->validated());

        return response()->json([
            'message' => 'Payment marked as failed successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }
}
