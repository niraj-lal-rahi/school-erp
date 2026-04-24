<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ledger_account_id' => $this->ledger_account_id,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'entry_date' => optional($this->entry_date)->toDateString(),
            'debit' => $this->debit,
            'credit' => $this->credit,
            'description' => $this->description,
            'ledger_account' => $this->whenLoaded('ledgerAccount', fn () => $this->ledgerAccount ? [
                'id' => $this->ledgerAccount->id,
                'name' => $this->ledgerAccount->name,
                'code' => $this->ledgerAccount->code,
                'account_type' => $this->ledgerAccount->account_type,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
