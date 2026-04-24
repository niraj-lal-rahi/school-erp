<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\FeeHeadData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertFeeHeadRequest;
use App\Http\Resources\Finance\FeeHeadResource;
use App\Models\Finance\FeeHead;
use App\Services\Finance\FeeHeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeHeadController extends Controller
{
    public function __construct(
        protected FeeHeadService $feeHeads,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FeeHead::class);

        return response()->json([
            'data' => FeeHeadResource::collection(
                $this->feeHeads->all($request->only(['search', 'status', 'fee_category_id']))
            ),
        ]);
    }

    public function store(UpsertFeeHeadRequest $request): JsonResponse
    {
        $feeHead = $this->feeHeads->create(FeeHeadData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'default_amount' => $request->validated('default_amount'),
            'is_refundable' => $request->boolean('is_refundable'),
            'is_optional' => $request->boolean('is_optional'),
        ]));

        return response()->json([
            'message' => 'Fee head created successfully.',
            'data' => new FeeHeadResource($feeHead->load('feeCategory')),
        ], 201);
    }

    public function show(FeeHead $feeHead): JsonResponse
    {
        $this->authorize('view', $feeHead);

        return response()->json([
            'data' => new FeeHeadResource($feeHead->load('feeCategory')),
        ]);
    }

    public function update(UpsertFeeHeadRequest $request, FeeHead $feeHead): JsonResponse
    {
        $feeHead = $this->feeHeads->update($feeHead, FeeHeadData::fromArray([
            ...$request->validated(),
            'school_id' => $feeHead->school_id,
            'default_amount' => $request->validated('default_amount'),
            'is_refundable' => $request->boolean('is_refundable'),
            'is_optional' => $request->boolean('is_optional'),
        ]));

        return response()->json([
            'message' => 'Fee head updated successfully.',
            'data' => new FeeHeadResource($feeHead),
        ]);
    }

    public function destroy(FeeHead $feeHead): JsonResponse
    {
        $this->authorize('delete', $feeHead);
        $this->feeHeads->delete($feeHead);

        return response()->json(null, 204);
    }
}
