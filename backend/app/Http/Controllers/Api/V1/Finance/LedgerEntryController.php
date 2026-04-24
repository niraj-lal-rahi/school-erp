<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\LedgerEntryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertLedgerEntryRequest;
use App\Http\Resources\Finance\LedgerEntryResource;
use App\Models\Finance\LedgerEntry;
use App\Services\Finance\LedgerEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LedgerEntryController extends Controller
{
    public function __construct(
        protected LedgerEntryService $ledgerEntries,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LedgerEntry::class);

        return response()->json(
            LedgerEntryResource::collection($this->ledgerEntries->paginate(
                $request->only(['search', 'ledger_account_id', 'entry_date_from', 'entry_date_to']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertLedgerEntryRequest $request): JsonResponse
    {
        $ledgerEntry = $this->ledgerEntries->create(LedgerEntryData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'debit' => $request->validated('debit') ?? 0,
            'credit' => $request->validated('credit') ?? 0,
        ]));

        return response()->json([
            'message' => 'Ledger entry created successfully.',
            'data' => new LedgerEntryResource($ledgerEntry),
        ], 201);
    }

    public function show(LedgerEntry $ledgerEntry): JsonResponse
    {
        $this->authorize('view', $ledgerEntry);

        return response()->json([
            'data' => new LedgerEntryResource($ledgerEntry->load('ledgerAccount')),
        ]);
    }

    public function update(UpsertLedgerEntryRequest $request, LedgerEntry $ledgerEntry): JsonResponse
    {
        $ledgerEntry = $this->ledgerEntries->update($ledgerEntry, LedgerEntryData::fromArray([
            ...$request->validated(),
            'school_id' => $ledgerEntry->school_id,
            'debit' => $request->validated('debit') ?? 0,
            'credit' => $request->validated('credit') ?? 0,
        ]));

        return response()->json([
            'message' => 'Ledger entry updated successfully.',
            'data' => new LedgerEntryResource($ledgerEntry),
        ]);
    }

    public function destroy(LedgerEntry $ledgerEntry): JsonResponse
    {
        $this->authorize('delete', $ledgerEntry);
        $this->ledgerEntries->delete($ledgerEntry);

        return response()->json(null, 204);
    }
}
