<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Requests\Payments\StorePaymentGatewayRequest;
use App\Http\Requests\Payments\UpdatePaymentGatewayRequest;
use App\Http\Resources\Payments\PaymentGatewayResource;
use App\Models\Payments\PaymentGateway;
use App\Services\Payments\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends PaymentController
{
    public function __construct(
        protected PaymentGatewayService $gateways,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PaymentGateway::class);

        return response()->json([
            'data' => PaymentGatewayResource::collection($this->gateways->paginate(
                $request->only(['school_id', 'provider', 'status', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StorePaymentGatewayRequest $request): JsonResponse
    {
        $this->authorize('create', PaymentGateway::class);

        $gateway = $this->gateways->create($request->validated());

        return response()->json([
            'message' => 'Payment gateway created successfully.',
            'data' => new PaymentGatewayResource($gateway),
        ], 201);
    }

    public function update(UpdatePaymentGatewayRequest $request, int $id): JsonResponse
    {
        $gateway = $this->gateways->findOrFail($id);
        $this->authorize('update', $gateway);
        $gateway = $this->gateways->update($gateway, $request->validated());

        return response()->json([
            'message' => 'Payment gateway updated successfully.',
            'data' => new PaymentGatewayResource($gateway),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $gateway = $this->gateways->findOrFail($id);
        $this->authorize('delete', $gateway);
        $this->gateways->delete($gateway);

        return response()->json(null, 204);
    }
}
