<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffQualificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'degree' => $this->degree,
            'institution' => $this->institution,
            'board_or_university' => $this->board_or_university,
            'specialization' => $this->specialization,
            'passing_year' => $this->passing_year,
            'percentage_or_grade' => $this->percentage_or_grade,
            'document_path' => $this->document_path,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
