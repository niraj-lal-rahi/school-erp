<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeHeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fee_category_id' => $this->fee_category_id,
            'name' => $this->name,
            'code' => $this->code,
            'amount_type' => $this->amount_type,
            'default_amount' => $this->default_amount,
            'is_refundable' => $this->is_refundable,
            'is_optional' => $this->is_optional,
            'status' => $this->status,
            'fee_category' => $this->whenLoaded('feeCategory', fn () => $this->feeCategory ? [
                'id' => $this->feeCategory->id,
                'name' => $this->feeCategory->name,
                'code' => $this->feeCategory->code,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
