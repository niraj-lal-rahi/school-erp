<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\RefundData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateRefundRequest;
use App\Http\Resources\Finance\RefundResource;
use App\Models\Finance\Refund;
use App\Services\Finance\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(
        protected RefundService $refunds,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Refund::class);

        return response()->json(
            RefundResource::collection(
                $this->refunds->paginate(
                    $request->only(['search', 'student_id', 'payment_id', 'status']),
                    (int) $request->integer('per_page', 15),
                )
            )->response()->getData(true)
        );
    }

    public function store(CreateRefundRequest $request): JsonResponse
    {
        $refund = $this->refunds->create(RefundData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Refund request created successfully.',
            'data' => new RefundResource($refund),
        ], 201);
    }

    public function show(Refund $refund): JsonResponse
    {
        $this->authorize('view', $refund);

        return response()->json([
            'data' => new RefundResource($refund->load(['payment', 'student', 'approver', 'processor'])),
        ]);
    }

    public function destroy(Refund $refund): JsonResponse
    {
        $this->authorize('delete', $refund);
        $this->refunds->delete($refund);

        return response()->json(null, 204);
    }

    public function approve(Refund $refund, Request $request): JsonResponse
    {
        $this->authorize('update', $refund);
        $refund = $this->refunds->approve($refund, $request->user()->id);

        return response()->json([
            'message' => 'Refund approved successfully.',
            'data' => new RefundResource($refund),
        ]);
    }

    public function process(Refund $refund, Request $request): JsonResponse
    {
        $this->authorize('update', $refund);
        $refund = $this->refunds->process($refund, $request->user()->id);

        return response()->json([
            'message' => 'Refund processed successfully.',
            'data' => new RefundResource($refund),
        ]);
    }
}
