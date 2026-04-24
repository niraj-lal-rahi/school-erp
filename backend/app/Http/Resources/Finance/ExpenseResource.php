<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_no' => $this->expense_no,
            'expense_category_id' => $this->expense_category_id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => optional($this->expense_date)->toDateString(),
            'payment_method' => $this->payment_method,
            'vendor_name' => $this->vendor_name,
            'reference_no' => $this->reference_no,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status,
            'approved_at' => optional($this->approved_at)->toAtomString(),
            'paid_at' => optional($this->paid_at)->toAtomString(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'code' => $this->category->code,
            ] : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
