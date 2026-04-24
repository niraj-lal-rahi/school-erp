<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FineRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'fee_head_id' => $this->fee_head_id,
            'fine_type' => $this->fine_type,
            'amount' => $this->amount,
            'grace_days' => $this->grace_days,
            'max_fine_amount' => $this->max_fine_amount,
            'status' => $this->status,
            'fee_head' => $this->whenLoaded('feeHead', fn () => $this->feeHead ? [
                'id' => $this->feeHead->id,
                'name' => $this->feeHead->name,
                'code' => $this->feeHead->code,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
