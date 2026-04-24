<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\LedgerAccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertLedgerAccountRequest;
use App\Http\Resources\Finance\LedgerAccountResource;
use App\Models\Finance\LedgerAccount;
use App\Services\Finance\LedgerAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LedgerAccountController extends Controller
{
    public function __construct(
        protected LedgerAccountService $ledgerAccounts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LedgerAccount::class);

        return response()->json([
            'data' => LedgerAccountResource::collection(
                $this->ledgerAccounts->all($request->only(['search', 'account_type', 'status']))
            ),
        ]);
    }

    public function store(UpsertLedgerAccountRequest $request): JsonResponse
    {
        $ledgerAccount = $this->ledgerAccounts->create(LedgerAccountData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Ledger account created successfully.',
            'data' => new LedgerAccountResource($ledgerAccount->load('parent')->loadCount('entries')),
        ], 201);
    }

    public function show(LedgerAccount $ledgerAccount): JsonResponse
    {
        $this->authorize('view', $ledgerAccount);

        return response()->json([
            'data' => new LedgerAccountResource($ledgerAccount->load('parent')->loadCount('entries')),
        ]);
    }

    public function update(UpsertLedgerAccountRequest $request, LedgerAccount $ledgerAccount): JsonResponse
    {
        $ledgerAccount = $this->ledgerAccounts->update($ledgerAccount, LedgerAccountData::fromArray([
            ...$request->validated(),
            'school_id' => $ledgerAccount->school_id,
        ]));

        return response()->json([
            'message' => 'Ledger account updated successfully.',
            'data' => new LedgerAccountResource($ledgerAccount),
        ]);
    }

    public function destroy(LedgerAccount $ledgerAccount): JsonResponse
    {
        $this->authorize('delete', $ledgerAccount);
        $this->ledgerAccounts->delete($ledgerAccount);

        return response()->json(null, 204);
    }
}
