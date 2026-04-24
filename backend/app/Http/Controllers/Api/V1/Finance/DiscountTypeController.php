<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\DiscountTypeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertDiscountTypeRequest;
use App\Http\Resources\Finance\DiscountTypeResource;
use App\Models\Finance\DiscountType;
use App\Services\Finance\DiscountTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountTypeController extends Controller
{
    public function __construct(
        protected DiscountTypeService $discountTypes,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DiscountType::class);

        return response()->json([
            'data' => DiscountTypeResource::collection(
                $this->discountTypes->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertDiscountTypeRequest $request): JsonResponse
    {
        $discountType = $this->discountTypes->create(DiscountTypeData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Discount type created successfully.',
            'data' => new DiscountTypeResource($discountType),
        ], 201);
    }

    public function show(DiscountType $discountType): JsonResponse
    {
        $this->authorize('view', $discountType);

        return response()->json([
            'data' => new DiscountTypeResource($discountType),
        ]);
    }

    public function update(UpsertDiscountTypeRequest $request, DiscountType $discountType): JsonResponse
    {
        $discountType = $this->discountTypes->update($discountType, DiscountTypeData::fromArray([
            ...$request->validated(),
            'school_id' => $discountType->school_id,
        ]));

        return response()->json([
            'message' => 'Discount type updated successfully.',
            'data' => new DiscountTypeResource($discountType),
        ]);
    }

    public function destroy(DiscountType $discountType): JsonResponse
    {
        $this->authorize('delete', $discountType);
        $this->discountTypes->delete($discountType);

        return response()->json(null, 204);
    }
}
