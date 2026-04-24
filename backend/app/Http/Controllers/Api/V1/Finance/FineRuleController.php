<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\FineRuleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertFineRuleRequest;
use App\Http\Resources\Finance\FineRuleResource;
use App\Models\Finance\FineRule;
use App\Services\Finance\FineRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FineRuleController extends Controller
{
    public function __construct(
        protected FineRuleService $fineRules,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FineRule::class);

        return response()->json([
            'data' => FineRuleResource::collection(
                $this->fineRules->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertFineRuleRequest $request): JsonResponse
    {
        $fineRule = $this->fineRules->create(FineRuleData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'grace_days' => $request->validated('grace_days') ?? 0,
        ]));

        return response()->json([
            'message' => 'Fine rule created successfully.',
            'data' => new FineRuleResource($fineRule->load('feeHead')),
        ], 201);
    }

    public function show(FineRule $fineRule): JsonResponse
    {
        $this->authorize('view', $fineRule);

        return response()->json([
            'data' => new FineRuleResource($fineRule->load('feeHead')),
        ]);
    }

    public function update(UpsertFineRuleRequest $request, FineRule $fineRule): JsonResponse
    {
        $fineRule = $this->fineRules->update($fineRule, FineRuleData::fromArray([
            ...$request->validated(),
            'school_id' => $fineRule->school_id,
            'grace_days' => $request->validated('grace_days') ?? 0,
        ]));

        return response()->json([
            'message' => 'Fine rule updated successfully.',
            'data' => new FineRuleResource($fineRule->load('feeHead')),
        ]);
    }

    public function destroy(FineRule $fineRule): JsonResponse
    {
        $this->authorize('delete', $fineRule);
        $this->fineRules->delete($fineRule);

        return response()->json(null, 204);
    }
}
