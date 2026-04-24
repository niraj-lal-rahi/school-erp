<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\ReceiptResource;
use App\Models\Finance\Receipt;
use App\Services\Finance\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(
        protected ReceiptService $receipts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Receipt::class);

        return response()->json(
            ReceiptResource::collection($this->receipts->paginate(
                $request->only(['search', 'student_id', 'receipt_date_from', 'receipt_date_to']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function show(Receipt $receipt): JsonResponse
    {
        $this->authorize('view', $receipt);

        return response()->json([
            'data' => new ReceiptResource($receipt->load(['payment', 'student', 'issuer'])),
        ]);
    }

    public function download(Receipt $receipt): JsonResponse
    {
        $this->authorize('view', $receipt);

        return response()->json([
            'message' => 'Receipt metadata retrieved successfully.',
            'data' => new ReceiptResource($receipt->load(['payment', 'student', 'issuer'])),
        ]);
    }
}
