<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Resources\Payments\PaymentReconciliationResource;
use App\Models\Payments\PaymentReconciliation;
use App\Services\Payments\PaymentReconciliationService;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentReconciliationController extends PaymentController
{
    public function __construct(
        protected PaymentReconciliationService $reconciliations,
        protected PaymentTransactionService $transactions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentReconciliation::class);

        return response()->json([
            'data' => PaymentReconciliationResource::collection($this->reconciliations->paginate(
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

    public function reconcile(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['required', Rule::in(['webhook', 'manual', 'gateway_api', 'bank_statement'])],
            'new_status' => ['required', Rule::in(['pending', 'initiated', 'successful', 'failed', 'cancelled', 'refunded', 'manually_verified'])],
            'remarks' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $transaction = $this->transactions->findOrFail($id);
        $this->authorize('create', [PaymentReconciliation::class, $transaction]);
        $reconciliation = $this->reconciliations->reconcile(
            $transaction,
            $validated['source'],
            $validated['new_status'],
            $request->user(),
            $validated['remarks'] ?? null,
            $validated['metadata'] ?? [],
        );

        return response()->json([
            'message' => 'Payment reconciled successfully.',
            'data' => new PaymentReconciliationResource($reconciliation),
        ], 201);
    }
}
